<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Mail\ResetPasswordMail;
use App\Models\Pharmacy;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data): array
    {
        $otp = rand(100000, 999999);

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ];

        $user = $this->userRepository->create($userData);

        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        return [
            'user_id' => $user->id,
            'message' => 'Registration successful. OTP sent to your email.'
        ];
    }

    public function verifyOtp(string $email, string $otp): array
    {
        $user = $this->userRepository->findByEmailAndOtp($email, $otp);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ];
        }

        $this->userRepository->verifyEmail($user);

        return [
            'success' => true,
            'message' => 'Email verified successfully'
        ];
    }

    public function resendOtp(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        if ($user->email_verified_at) {
            return [
                'success' => false,
                'message' => 'Email already verified'
            ];
        }

        $otp = rand(100000, 999999);

        $this->userRepository->updateOtp($user, $otp);

        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        return [
            'success' => true,
            'message' => 'New OTP sent to your email'
        ];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        if (!$user->email_verified_at) {
            return [
                'success' => false,
                'message' => 'Please verify your email first'
            ];
        }

        if ($user->role === 'pharmacy') {
            $pharmacy = Pharmacy::where('email', $user->email)->first();

            if (!$pharmacy || !$pharmacy->is_active) {
                return [
                    'success' => false,
                    'message' => 'Your pharmacy account is pending admin approval'
                ];
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ];
    }

    public function forgotPassword(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $otp = rand(100000, 999999);

        $this->userRepository->updateOtp($user, $otp);

        Mail::to($user->email)->send(new ResetPasswordMail($otp, $user->name));

        return [
            'success' => true,
            'message' => 'Password reset OTP sent to your email'
        ];
    }

    public function resetPassword(string $email, string $otp, string $password): array
    {
        $user = $this->userRepository->findByEmailAndOtp($email, $otp);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ];
        }

        $this->userRepository->resetPassword($user, $password);

        return [
            'success' => true,
            'message' => 'Password reset successful. Please login with your new password.'
        ];
    }

}
