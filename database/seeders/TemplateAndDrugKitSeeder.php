<?php

namespace Database\Seeders;

use App\Models\DrugKit;
use App\Models\PrescriptionTemplate;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TemplateAndDrugKitSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();
        if ($tenants->isEmpty()) {
            $tenants = collect([
                Tenant::create([
                    'slug' => 'dr-mahbubur',
                    'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
                    'bmdc_no' => 'A-45210',
                    'status' => 'active',
                    'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology), BCS (Health)',
                    'doctor_designation' => 'Professor & Chief Consultant, Department of Nephrology',
                    'specialty' => 'Nephrology / CKD Specialist',
                    'doctor_description' => 'Professor and Head of Nephrology. Dedicated kidney care and dialysis management.',
                    'brand_color' => '#0891b2',
                ]),
            ]);
        }

        foreach ($tenants as $tenant) {
            // Seed Demo Clinical Templates
            PrescriptionTemplate::withoutGlobalScope('tenant')->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => '⚡ 1-Click CKD Stage 4 Full Profile'],
                [
                    'specialty' => 'Nephrology / CKD Specialist',
                    'chief_complaints' => "• Generalized body swelling (Edema) x 1 month\n• Decreased urine output (Oliguria)\n• Uncontrolled High Blood Pressure\n• Fatigue, nausea & loss of appetite",
                    'clinical_findings' => 'Bilateral pitting pedal edema (++), Facial puffiness, Pallor (+), BP: 160/95 mmHg, Pulse: 80/min, Chest: bilateral basal crepitations',
                    'diagnosis' => 'Chronic Kidney Disease (Stage 4), Hypertensive Nephrosclerosis, Renal Anemia',
                    'investigations' => 'S. Creatinine & eGFR, Blood Urea Nitrogen (BUN), Serum Electrolytes (Na+, K+, Cl-, HCO3-), Urine R/M/E with Spot ACR, USG of KUB with PVR, CBC with ESR, iPTH, Serum Calcium & Phosphate',
                    'advice' => "• Low protein diet (0.6 - 0.8 g/kg/day)\n• Fluid restriction to 1.5 Liters/day\n• Strict low salt diet (< 2 gm/day)\n• Avoid NSAIDs & nephrotoxic painkillers strictly\n• Avoid high potassium foods (banana, coconut water, green leaves)\n• Target BP < 130/80 mmHg\n• Follow-up after 15 days with S. Creatinine & Electrolytes report",
                ]
            );

            PrescriptionTemplate::withoutGlobalScope('tenant')->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => '⚡ 1-Click Hypertension & Angina Protocol'],
                [
                    'specialty' => 'Cardiology',
                    'chief_complaints' => "• Exertional chest tightness radiating to left shoulder x 2 weeks\n• Shortness of breath on climbing stairs\n• Occasional palpitations and headache",
                    'clinical_findings' => 'BP: 155/95 mmHg, Pulse: 88/min regular, S1 + S2 audible, no murmurs, Lungs clear',
                    'diagnosis' => 'Ischaemic Heart Disease (Stable Angina), Essential Hypertension (Stage 2), Dyslipidaemia',
                    'investigations' => '12-lead ECG, Echocardiography (2D & Doppler), Fasting Lipid Profile, Troponin-I, S. Creatinine, RBS',
                    'advice' => "• Restrict dietary salt (< 3 gm/day)\n• Avoid oily, fried foods and beef/mutton strictly\n• 30 mins brisk walking daily\n• Keep Tab. Nitromint spray handy in pocket\n• Follow-up after 15 days with ECG and Lipid profile",
                ]
            );

            PrescriptionTemplate::withoutGlobalScope('tenant')->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => '⚡ 1-Click Acute Viral & Fever Protocol'],
                [
                    'specialty' => 'General Medicine',
                    'chief_complaints' => "• High grade fever (up to 103°F) with chills x 4 days\n• Severe body ache, retro-orbital headache\n• Loss of appetite, nausea and malaise",
                    'clinical_findings' => 'Temp: 102.4°F, BP: 115/75 mmHg, Pulse: 96/min, Tongue coated, Throat congested, No skin rash',
                    'diagnosis' => 'Acute Viral Syndrome (Query Dengue Fever / Enteric Fever)',
                    'investigations' => 'CBC with Platelet count, Dengue NS1 Antigen, Urine R/M/E, Widal test',
                    'advice' => "• Complete bed rest\n• Frequent oral fluid intake (coconut water, soup, ORS - at least 2.5 L/day)\n• Sponge with normal water if temp > 101°F\n• Avoid Aspirin / Ibuprofen strictly\n• Repeat Platelet count after 48 hours",
                ]
            );

            // Seed Demo Drug Kits
            DrugKit::withoutGlobalScope('tenant')->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => '💊 CKD Core Drug Kit (4 Meds)'],
                [
                    'specialty' => 'Nephrology / CKD Specialist',
                    'items' => [
                        ['type' => 'Tab.', 'name' => 'Ketosteril', 'generic' => 'Alpha-Ketoanalogues', 'strength' => '600mg', 'dosage' => '1+1+1', 'duration' => '1 month', 'instruction' => 'With meal (খাবারের সাথে)'],
                        ['type' => 'Tab.', 'name' => 'Sodibicarb', 'generic' => 'Sodium Bicarbonate', 'strength' => '500mg', 'dosage' => '1+0+1', 'duration' => '1 month', 'instruction' => 'After meal (খাবার পরে)'],
                        ['type' => 'Tab.', 'name' => 'Febux', 'generic' => 'Febuxostat', 'strength' => '40mg', 'dosage' => '0+0+1', 'duration' => '1 month', 'instruction' => 'After meal (খাবার পরে)'],
                        ['type' => 'Tab.', 'name' => 'Calcichew D', 'generic' => 'Calcium + Vit D3', 'strength' => '500mg+200IU', 'dosage' => '0+0+1', 'duration' => '1 month', 'instruction' => 'After meal (খাবার পরে)'],
                    ],
                ]
            );

            DrugKit::withoutGlobalScope('tenant')->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => '💊 Heart & BP Core Kit'],
                [
                    'specialty' => 'Cardiology',
                    'items' => [
                        ['type' => 'Tab.', 'name' => 'Amlopin', 'generic' => 'Amlodipine', 'strength' => '5mg', 'dosage' => '1+0+0', 'duration' => '1 month', 'instruction' => 'Morning (সকালে)'],
                        ['type' => 'Tab.', 'name' => 'Bizoran', 'generic' => 'Amlodipine + Olmesartan', 'strength' => '5/20mg', 'dosage' => '0+0+1', 'duration' => '1 month', 'instruction' => 'Night (রাতে)'],
                        ['type' => 'Tab.', 'name' => 'Concor', 'generic' => 'Bisoprolol', 'strength' => '2.5mg', 'dosage' => '1+0+0', 'duration' => '1 month', 'instruction' => 'Morning after meal'],
                    ],
                ]
            );

            DrugKit::withoutGlobalScope('tenant')->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => '💊 Viral Fever & Bodyache Kit'],
                [
                    'specialty' => 'General Medicine',
                    'items' => [
                        ['type' => 'Tab.', 'name' => 'Napa Extra', 'generic' => 'Paracetamol + Caffeine', 'strength' => '500+65mg', 'dosage' => '1+1+1', 'duration' => '5 days', 'instruction' => 'After meal (খাবার পরে)'],
                        ['type' => 'Cap.', 'name' => 'Protonix', 'generic' => 'Pantoprazole', 'strength' => '20mg', 'dosage' => '1+0+1', 'duration' => '7 days', 'instruction' => 'Before meal (খাবার আগে)'],
                        ['type' => 'Tab.', 'name' => 'Alarid', 'generic' => 'Cetirizine', 'strength' => '10mg', 'dosage' => '0+0+1', 'duration' => '5 days', 'instruction' => 'Night (রাতে)'],
                    ],
                ]
            );
        }
    }
}
