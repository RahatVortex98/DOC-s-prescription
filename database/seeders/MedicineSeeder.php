<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('database/data/medicines.csv');

        if (! File::exists(dirname($csvPath))) {
            File::makeDirectory(dirname($csvPath), 0755, true);
        }

        $sampleData = [
            ['dosage_form', 'brand_name', 'generic_name', 'strength', 'manufacturer'],

            // Paracetamol Variants (Napa, Ace)
            ['Tab.', 'Napa', 'Paracetamol', '500mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Tab.', 'Napa Extra', 'Paracetamol + Caffeine', '500mg+65mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Tab.', 'Napa Extend', 'Paracetamol', '665mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Tab.', 'Napa One', 'Paracetamol', '1000mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Tab.', 'Napa Rapid', 'Paracetamol (Actizorb tech)', '500mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Syr.', 'Napa Suspension', 'Paracetamol', '120mg/5ml', 'Beximco Pharmaceuticals Ltd.'],
            ['Drop', 'Napa Paediatric Drop', 'Paracetamol', '80mg/0.8ml', 'Beximco Pharmaceuticals Ltd.'],
            ['Supp.', 'Napa Suppository', 'Paracetamol', '125mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Supp.', 'Napa Suppository', 'Paracetamol', '250mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Supp.', 'Napa Suppository', 'Paracetamol', '500mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Inj.', 'Napa IV Infusion', 'Paracetamol', '10mg/ml (100ml)', 'Beximco Pharmaceuticals Ltd.'],

            ['Tab.', 'Ace', 'Paracetamol', '500mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Ace Plus', 'Paracetamol + Caffeine', '500mg+65mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Ace Power', 'Paracetamol', '1000mg', 'Square Pharmaceuticals PLC'],
            ['Syr.', 'Ace Suspension', 'Paracetamol', '120mg/5ml', 'Square Pharmaceuticals PLC'],
            ['Supp.', 'Ace Suppository', 'Paracetamol', '250mg', 'Square Pharmaceuticals PLC'],

            // CKD & Nephrology Core Medicines
            ['Tab.', 'Ketosteril', 'Alpha-Ketoanalogues to Essential Amino Acids', '600mg', 'Fresenius Kabi Bangladesh'],
            ['Tab.', 'Ulsicral', 'Sucralfate', '500mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Sodibicarb', 'Sodium Bicarbonate', '500mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Sodik', 'Sodium Bicarbonate', '500mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Inj.', 'Eprex', 'Erythropoietin Alfa (rDNA)', '4000 IU/0.4ml', 'Janssen / Incepta'],
            ['Inj.', 'Epotin', 'Recombinant Human Erythropoietin', '4000 IU', 'Square Pharmaceuticals PLC'],
            ['Inj.', 'Epotin', 'Recombinant Human Erythropoietin', '10000 IU', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Febux', 'Febuxostat', '40mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Febux', 'Febuxostat', '80mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Feburic', 'Febuxostat', '40mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Cap.', 'Rocaltrol', 'Calcitriol', '0.25mcg', 'Roche / Square Pharmaceuticals PLC'],
            ['Cap.', 'Bonitrol', 'Calcitriol', '0.25mcg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Torsid', 'Torsemide', '10mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Torsid', 'Torsemide', '20mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Lasix', 'Furosemide', '40mg', 'Sanofi Bangladesh Ltd.'],
            ['Inj.', 'Lasix', 'Furosemide', '20mg/2ml', 'Sanofi Bangladesh Ltd.'],
            ['Tab.', 'Renvela', 'Sevelamer Carbonate', '800mg', 'Sanofi / Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Sevlacar', 'Sevelamer Carbonate', '800mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Calcichew D', 'Calcium Carbonate + Cholecalciferol', '500mg+200IU', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Coralcal D', 'Coral Calcium + Vitamin D3', '500mg+200IU', 'Radiant Pharmaceuticals Ltd.'],
            ['Inj.', 'Ferosig', 'Iron Sucrose', '100mg/5ml', 'Incepta Pharmaceuticals Ltd.'],
            ['Inj.', 'Venofer', 'Iron Sucrose', '100mg/5ml', 'Vifor Pharma / Square'],

            // Cardiovascular & Hypertension (Often in CKD Patients)
            ['Tab.', 'Amlopin', 'Amlodipine Besylate', '5mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Amlopin', 'Amlodipine Besylate', '10mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Bizoran', 'Amlodipine + Olmesartan', '5mg/20mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Bizoran', 'Amlodipine + Olmesartan', '5mg/40mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Olmesan', 'Olmesartan Medoxomil', '20mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Tab.', 'Olmesan', 'Olmesartan Medoxomil', '40mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Tab.', 'Angilock', 'Losartan Potassium', '50mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Concor', 'Bisoprolol Fumarate', '2.5mg', 'Merck / Healthcare Pharmaceuticals'],
            ['Tab.', 'Concor', 'Bisoprolol Fumarate', '5mg', 'Merck / Healthcare Pharmaceuticals'],
            ['Tab.', 'Cardizem', 'Diltiazem Hydrochloride', '30mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Maxflow', 'Tamsulosin Hydrochloride', '0.4mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Tab.', 'Uromax', 'Tamsulosin Hydrochloride', '0.4mg', 'Square Pharmaceuticals PLC'],

            // Gastrointestinal (Gastritis / PPI)
            ['Cap.', 'Protonix', 'Pantoprazole Sodium', '20mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Protonix', 'Pantoprazole Sodium', '40mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Inj.', 'Protonix IV', 'Pantoprazole Sodium', '40mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Cap.', 'Seclo', 'Omeprazole', '20mg', 'Square Pharmaceuticals PLC'],
            ['Cap.', 'Seclo', 'Omeprazole', '40mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Sergel', 'Esomeprazole Magnesium', '20mg', 'Healthcare Pharmaceuticals Ltd.'],
            ['Tab.', 'Sergel', 'Esomeprazole Magnesium', '40mg', 'Healthcare Pharmaceuticals Ltd.'],
            ['Tab.', 'Maxpro', 'Esomeprazole Magnesium', '20mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Maxpro', 'Esomeprazole Magnesium', '40mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Finix', 'Rabeprazole Sodium', '20mg', 'Opsonin Pharma Ltd.'],
            ['Tab.', 'Motigut', 'Domperidone', '10mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Deflux', 'Domperidone', '10mg', 'Beximco Pharmaceuticals Ltd.'],
            ['Syr.', 'Antacid Plus', 'Magnesium Hydroxide + Aluminium Hydroxide + Simethicone', '200ml', 'Square Pharmaceuticals PLC'],

            // Respiratory & Allergy
            ['Tab.', 'Monas', 'Montelukast Sodium', '10mg', 'Acme Laboratories Ltd.'],
            ['Tab.', 'Monas', 'Montelukast Sodium', '4mg (Chewable)', 'Acme Laboratories Ltd.'],
            ['Tab.', 'Montene', 'Montelukast Sodium', '10mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Alarid', 'Cetirizine Dihydrochloride', '10mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Fexo', 'Fexofenadine Hydrochloride', '120mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Fexo', 'Fexofenadine Hydrochloride', '180mg', 'Square Pharmaceuticals PLC'],
            ['Inhaler', 'Bexitrol-F', 'Salmeterol + Fluticasone Propionate', '25mcg/125mcg', 'Beximco Pharmaceuticals Ltd.'],
            ['Inhaler', 'Ventolin Evohaler', 'Salbutamol', '100mcg', 'GSK Bangladesh'],

            // Diabetes (Oral Hypoglycemic Agents)
            ['Tab.', 'Comet', 'Metformin Hydrochloride', '500mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Comet XR', 'Metformin Hydrochloride', '1000mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Linaglip', 'Linagliptin', '5mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Linaglip-M', 'Linagliptin + Metformin', '2.5mg/500mg', 'Incepta Pharmaceuticals Ltd.'],
            ['Tab.', 'Jardiance', 'Empagliflozin', '10mg', 'Boehringer Ingelheim / Square'],
            ['Tab.', 'Jardiance', 'Empagliflozin', '25mg', 'Boehringer Ingelheim / Square'],
            ['Tab.', 'D-Glif', 'Dapagliflozin', '10mg', 'Incepta Pharmaceuticals Ltd.'],

            // Vitamins & Supplements
            ['Tab.', 'Neobion', 'Vitamin B1 + B6 + B12', '100mg+200mg+200mcg', 'Square Pharmaceuticals PLC'],
            ['Inj.', 'Neobion', 'Vitamin B1 + B6 + B12', '3ml', 'Square Pharmaceuticals PLC'],
            ['Cap.', 'Neuro-B', 'Vitamin B1 + B6 + B12', 'Standard', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Ceevit', 'Ascorbic Acid (Vitamin C)', '250mg', 'Square Pharmaceuticals PLC'],
            ['Cap.', 'D-Rise', 'Cholecalciferol (Vitamin D3)', '20000 IU', 'Square Pharmaceuticals PLC'],
            ['Cap.', 'D-Rise', 'Cholecalciferol (Vitamin D3)', '40000 IU', 'Square Pharmaceuticals PLC'],

            // Antibiotics
            ['Inj.', 'Ceftri', 'Ceftriaxone', '500mg', 'Renata Limited'],
            ['Inj.', 'Ceftri', 'Ceftriaxone', '1g', 'Renata Limited'],
            ['Cap.', 'Moxacil', 'Amoxicillin Trihydrate', '500mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Zimax', 'Azithromycin', '500mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Ciprocin', 'Ciprofloxacin', '500mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Cef-3', 'Cefixime Trihydrate', '200mg', 'Square Pharmaceuticals PLC'],
            ['Tab.', 'Cef-3', 'Cefixime Trihydrate', '400mg', 'Square Pharmaceuticals PLC'],
        ];

        $fp = fopen($csvPath, 'w');
        if ($fp !== false) {
            foreach ($sampleData as $line) {
                fputcsv($fp, $line);
            }
            fclose($fp);
        }

        // Truncate existing medicines table first to reseed cleanly
        DB::table('medicines')->truncate();

        Artisan::call('medicines:import', ['file' => 'database/data/medicines.csv']);
    }
}
