<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Demo Doctor Tenant
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'dr-mahbubur'],
            [
                'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
                'bmdc_no' => 'A-45210',
                'status' => 'active',
                'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology), BCS (Health)',
                'doctor_designation' => 'Professor & Chief Consultant, Department of Nephrology',
                'specialty' => 'Nephrology / CKD Specialist',
                'doctor_description' => 'Professor and Head of Nephrology. Over 20 years of clinical experience in advanced kidney diseases, hypertension, and dialysis therapy.',
                'brand_color' => '#1e3fae',
            ]
        );

        // 2. Doctor Login Account
        User::updateOrCreate(
            ['email' => 'doctor@bikrify.com'],
            [
                'name' => 'Prof. Dr. Mahbubur Rahman',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'role' => 'doctor',
            ]
        );

        // 3. Assistant / Receptionist Account
        User::updateOrCreate(
            ['email' => 'assistant@bikrify.com'],
            [
                'name' => 'Clinic Assistant',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'role' => 'assistant',
            ]
        );

        // 4. Bikrify Super Admin Account
        User::updateOrCreate(
            ['email' => 'admin@bikrify.com'],
            [
                'name' => 'Bikrify Super Admin',
                'password' => Hash::make('password'),
                'tenant_id' => null,
                'role' => 'super_admin',
            ]
        );
    }
}
