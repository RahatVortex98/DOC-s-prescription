<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Chamber;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $chamber = Chamber::first();
        if (! $chamber) {
            return;
        }

        Appointment::firstOrCreate(
            [
                'chamber_id' => $chamber->id,
                'patient_name' => 'Abdur Razzaq',
                'appointment_date' => today(),
            ],
            [
                'serial_no' => 1,
                'patient_phone' => '01711002233',
                'patient_age' => 52,
                'patient_gender' => 'Male',
                'appointment_time' => '04:15 PM',
                'status' => 'waiting',
                'notes' => 'Complains of swollen feet and high blood pressure for 2 weeks.',
            ]
        );

        Appointment::firstOrCreate(
            [
                'chamber_id' => $chamber->id,
                'patient_name' => 'Nasima Begum',
                'appointment_date' => today(),
            ],
            [
                'serial_no' => 2,
                'patient_phone' => '01819445566',
                'patient_age' => 46,
                'patient_gender' => 'Female',
                'appointment_time' => '04:30 PM',
                'status' => 'waiting',
                'notes' => 'Diabetic patient, routine kidney function checkup.',
            ]
        );

        Appointment::firstOrCreate(
            [
                'chamber_id' => $chamber->id,
                'patient_name' => 'Tanvir Hasan',
                'appointment_date' => today(),
            ],
            [
                'serial_no' => 3,
                'patient_phone' => '01911778899',
                'patient_age' => 38,
                'patient_gender' => 'Male',
                'appointment_time' => '04:45 PM',
                'status' => 'waiting',
                'notes' => 'Follow up after 1 month with Serum Creatinine report.',
            ]
        );
    }
}
