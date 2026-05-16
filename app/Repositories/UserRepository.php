<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
    
    public function findByEmailAndOtp(string $email, string $otp): ?User
    {
        return User::where('email', $email)
            ->where('otp', $otp)
            ->where('otp_expires_at', '>', now())
            ->first();
    }
    
    public function verifyEmail(User $user): void
    {
        $user->update([
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    public function updateOtp(User $user, string $otp): void
    {
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();
    }

    public function updateResetOtp(User $user, string $otp): void
    {
        $user->reset_otp = $otp;
        $user->reset_otp_expires_at = now()->addMinutes(15);
        $user->save();
    }

    public function resetPassword(User $user, string $password): void
    {
        $user->password = Hash::make($password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();
    }

}