<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('database/data/medicines.csv');

        if (! File::exists(dirname($csvPath))) {
            File::makeDirectory(dirname($csvPath), 0755, true);
        }

        // Provide verified sample BD DGDA records matching the reference document
        if (! File::exists($csvPath)) {
            $sampleData = [
                ['dosage_form', 'brand_name', 'generic_name', 'strength', 'manufacturer'],
                ['Cap.', 'Protonix', 'Pantoprazole', '20mg', 'Incepta Pharmaceuticals Ltd.'],
                ['Tab.', 'Neobion', 'Vitamin B1 + B6 + B12', '100mg+200mg+200mcg', 'Square Pharmaceuticals PLC'],
                ['Tab.', 'Maxflow', 'Tamsulosin Hydrochloride', '0.4mg', 'Beximco Pharmaceuticals Ltd.'],
                ['Inj.', 'Ceftri', 'Ceftriaxone', '500mg', 'Renata Limited'],
                ['Tab.', 'Napa Extend', 'Paracetamol', '665mg', 'Beximco Pharmaceuticals Ltd.'],
                ['Tab.', 'Ace Plus', 'Paracetamol + Caffeine', '500mg+65mg', 'Square Pharmaceuticals PLC'],
                ['Cap.', 'Seclo', 'Omeprazole', '20mg', 'Square Pharmaceuticals PLC'],
                ['Tab.', 'Monas', 'Montelukast', '10mg', 'Acme Laboratories Ltd.'],
                ['Tab.', 'Bizoran', 'Amlodipine + Olmesartan', '5mg/20mg', 'Square Pharmaceuticals PLC'],
            ];

            $fp = fopen($csvPath, 'w');
            if ($fp !== false) {
                foreach ($sampleData as $line) {
                    fputcsv($fp, $line);
                }
                fclose($fp);
            }
        }

        Artisan::call('medicines:import', ['file' => 'database/data/medicines.csv']);
    }
}
