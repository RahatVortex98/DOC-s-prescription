<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TenantUserSeeder::class,
            ChamberSeeder::class,
            MedicineSeeder::class,
            AppointmentSeeder::class,
            TemplateAndDrugKitSeeder::class,
        ]);
    }
}
