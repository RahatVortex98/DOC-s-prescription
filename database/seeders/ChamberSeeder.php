<?php

namespace Database\Seeders;

use App\Models\Chamber;
use Illuminate\Database\Seeder;

class ChamberSeeder extends Seeder
{
    public function run(): void
    {
        Chamber::firstOrCreate(
            ['name' => 'Bikrify Digital Health Care - Banani'],
            [
                'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
                'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology), BCS (Health)',
                'doctor_designation' => 'Professor & Chief Consultant, Department of Nephrology',
                'specialty' => 'Nephrology / CKD Specialist',
                'address' => 'House 45, Road 11, Block D, Banani, Dhaka-1213',
                'phone' => '+880 1711-223344',
                'visiting_hours' => '4:00 PM - 8:00 PM (Sat - Thu)',
                'is_default' => true,
            ]
        );

        Chamber::firstOrCreate(
            ['name' => 'Popular Diagnostic Center - Dhanmondi'],
            [
                'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
                'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology), BCS (Health)',
                'doctor_designation' => 'Visiting Senior Consultant (Kidney & Medicine)',
                'specialty' => 'Nephrology / CKD Specialist',
                'address' => 'House 16, Road 2, Dhanmondi R/A, Dhaka-1205',
                'phone' => '+880 1819-556677',
                'visiting_hours' => '8:00 PM - 10:30 PM (Sat, Mon, Wed)',
                'is_default' => false,
            ]
        );

        Chamber::firstOrCreate(
            ['name' => 'Square Hospital & Medical Center'],
            [
                'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
                'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology), BCS (Health)',
                'doctor_designation' => 'Senior Consultant Nephrologist',
                'specialty' => 'Nephrology / CKD Specialist',
                'address' => '18/F Bir Uttam Qazi Nuruzzaman Sarak, West Panthapath, Dhaka',
                'phone' => '+880 1713-377773',
                'visiting_hours' => '10:00 AM - 1:00 PM (Sun, Tue, Thu)',
                'is_default' => false,
            ]
        );
    }
}
