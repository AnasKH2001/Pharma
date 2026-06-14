<?php

namespace App\Services;

use App\Repositories\PharmacyRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class PharmacyService
{
    protected PharmacyRepository $pharmacyRepository;
    protected UserRepository $userRepository;

    public function __construct(
        PharmacyRepository $pharmacyRepository,
        UserRepository $userRepository
    ) {
        $this->pharmacyRepository = $pharmacyRepository;
        $this->userRepository = $userRepository;
    }

    public function register(array $data, array $credentialFiles): array
    {
        $otp = rand(100000, 999999);

        // Create user
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'pharmacy',
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ];

        $user = $this->userRepository->create($userData);


        $credentialsFolder = 'pharmacy_credentials/pharmacy_' . $user->id;

        foreach ($credentialFiles as $file) {
            $file->storeAs($credentialsFolder, $file->getClientOriginalName(), 'public');
        }

        // Create pharmacy
        $pharmacyData = [
            'name' => $data['name'],
            'credentials' => $credentialsFolder,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'address' => $data['address'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'opens_at' => $data['opens_at'],
            'closes_at' => $data['closes_at'],
            'is_active' => false,
        ];

        $pharmacy = $this->pharmacyRepository->create($pharmacyData);

        // Send OTP email
        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        return [
            'user_id' => $user->id,
            'pharmacy_id' => $pharmacy->id,
            'message' => 'Pharmacy registration successful. OTP sent to your email. Account will be active after admin approval.'
        ];
    }
}
