<?php

namespace Database\Seeders;

use App\Models\Chamber;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ChamberSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first() ?? Tenant::create([
            'slug' => 'dr-mahbubur',
            'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
            'bmdc_no' => 'A-45210',
            'status' => 'active',
        ]);

        Chamber::withoutGlobalScope('tenant')->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Bikrify Digital Health Care - Banani'],
            [
                'hospital_affiliation' => 'Bikrify Specialist Care',
                'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
                'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology), BCS (Health)',
                'doctor_designation' => 'Professor & Chief Consultant, Department of Nephrology',
                'specialty' => 'Nephrology / CKD Specialist',
                'address' => 'House 45, Road 11, Block D, Banani, Dhaka-1213',
                'room_number' => 'Chamber #302 (3rd Floor)',
                'phone' => '+880 1711-223344',
                'secondary_phone' => '+880 1911-334455',
                'whatsapp_number' => '+8801711223344',
                'visiting_hours' => '4:00 PM - 8:00 PM (Sat - Thu)',
                'is_default' => true,
            ]
        );

        Chamber::withoutGlobalScope('tenant')->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Popular Diagnostic Center - Dhanmondi'],
            [
                'hospital_affiliation' => 'Popular Medical College & Hospital',
                'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
                'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology), BCS (Health)',
                'doctor_designation' => 'Visiting Senior Consultant (Kidney & Medicine)',
                'specialty' => 'Nephrology / CKD Specialist',
                'address' => 'House 16, Road 2, Dhanmondi R/A, Dhaka-1205',
                'room_number' => 'Room #405, Building 2',
                'phone' => '+880 1819-556677',
                'secondary_phone' => '+880 1712-998877',
                'whatsapp_number' => '+8801819556677',
                'visiting_hours' => '8:00 PM - 10:30 PM (Sat, Mon, Wed)',
                'is_default' => false,
            ]
        );

        Chamber::withoutGlobalScope('tenant')->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Square Hospital & Medical Center'],
            [
                'hospital_affiliation' => 'Square Hospitals Ltd.',
                'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
                'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology), BCS (Health)',
                'doctor_designation' => 'Senior Consultant & Nephrologist',
                'specialty' => 'Nephrology / CKD Specialist',
                'address' => '18F, Bir Uttam Qazi Nuruzzaman Sarak, West Panthapath, Dhaka',
                'room_number' => 'OPD-2, Room #312',
                'phone' => '+880 1713-000111',
                'secondary_phone' => '+880 1819-222333',
                'whatsapp_number' => '+8801713000111',
                'visiting_hours' => '6:00 PM - 9:00 PM (Sun, Tue, Thu)',
                'is_default' => false,
            ]
        );
    }
}
