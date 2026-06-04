<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pharmacy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@pharma.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Customer
        User::firstOrCreate(
            ['email' => 'customer@pharma.com'],
            [
                'name' => 'Ahmed Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        // Supplier
        User::firstOrCreate(
            ['email' => 'supplier@pharma.com'],
            [
                'name' => 'Supplier Company',
                'password' => Hash::make('password'),
                'role' => 'supplier',
                'email_verified_at' => now(),
            ]
        );

        // Pharmacy (approved)
        $pharmacyUser = User::firstOrCreate(
            ['email' => 'pharmacy@pharma.com'],
            [
                'name' => 'City Pharmacy',
                'password' => Hash::make('password'),
                'role' => 'pharmacy',
                'email_verified_at' => now(),
            ]
        );

        Pharmacy::firstOrCreate(
            ['email' => 'pharmacy@pharma.com'],
            [
                'name' => 'City Pharmacy',
                'credentials' => 'pharmacy_credentials/pharmacy_' . $pharmacyUser->id,
                'phone' => '0111234567',
                'address' => 'Damascus, Syria',
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'opens_at' => '09:00:00',
                'closes_at' => '21:00:00',
                'is_active' => true,
            ]
        );

        // Pharmacy (pending approval)
        $pendingUser = User::firstOrCreate(
            ['email' => 'pending@pharma.com'],
            [
                'name' => 'New Pharmacy',
                'password' => Hash::make('password'),
                'role' => 'pharmacy',
                'email_verified_at' => now(),
            ]
        );

        Pharmacy::firstOrCreate(
            ['email' => 'pending@pharma.com'],
            [
                'name' => 'New Pharmacy',
                'credentials' => 'pharmacy_credentials/pharmacy_' . $pendingUser->id,
                'phone' => '0117654321',
                'address' => 'Aleppo, Syria',
                'latitude' => 36.2021,
                'longitude' => 37.1343,
                'opens_at' => '10:00:00',
                'closes_at' => '22:00:00',
                'is_active' => false,
            ]
        );
    }
}