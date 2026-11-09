<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PrescriptionBuilder extends Component
{
    // Patient Details
    #[Locked]
    public ?int $selected_patient_id = null;

    public bool $is_existing_patient = false;

    public string $patient_search = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $patientSearchResults = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $patientHistory = [];

    public bool $showHistoryModal = false;

    public string $patient_name = '';

    public string $patient_age = '';

    public string $patient_gender = 'Male';

    public string $patient_phone = '';

    // Active Appointment Tracking
    #[Locked]
    public ?int $current_appointment_id = null;

    // Doctor & Chamber Details
    #[Locked]
    public ?int $selected_chamber_id = null;

    public string $doctor_name = 'Prof. Dr. Mahbubur Rahman';

    public string $doctor_qualification = 'MBBS, FCPS, MD (Nephrology), BCS (Health)';

    public string $doctor_designation = 'Professor & Chief Consultant, Department of Nephrology';

    public string $hospital_affiliation = 'Bikrify Specialist Care';

    public string $chamber_name = 'Bikrify Digital Health Care';

    public string $chamber_address = 'House 45, Road 11, Block D, Banani, Dhaka';

    public string $room_number = 'Chamber #302';

    public string $chamber_phone = '+880 1711-223344';

    public string $secondary_phone = '+880 1911-334455';

    public string $whatsapp_number = '+8801711223344';

    public string $chamber_visiting_hours = '4:00 PM - 8:00 PM (Sat - Thu)';

    public string $specialty = 'Nephrology / CKD Specialist';

    public bool $showChamberModal = false;

    // Chamber Form Details (Bikrify Onboarding / Setup)
    public ?int $chamber_form_id = null;

    public string $chamber_form_name = '';

    public string $chamber_form_hospital_affiliation = '';

    public string $chamber_form_doctor_name = '';

    public string $chamber_form_doctor_qualification = '';

    public string $chamber_form_doctor_designation = '';

    public string $chamber_form_specialty = '';

    public string $chamber_form_address = '';

    public string $chamber_form_room_number = '';

    public string $chamber_form_phone = '';

    public string $chamber_form_secondary_phone = '';

    public string $chamber_form_whatsapp_number = '';

    public string $chamber_form_visiting_hours = '';

    // Vitals Fast Entry Bar
    public string $vital_bp_systolic = '';

    public string $vital_bp_diastolic = '';

    public string $vital_pulse = '';

    public string $vital_weight = '';

    public string $vital_height_cm = '';

    public string $vital_rbs = '';

    // Clinical Details
    public string $chief_complaints = '';

    public string $clinical_findings = '';

    public string $diagnosis = '';

    public string $investigations = '';

    public string $advice = '';

    // Medicine Search & Items
    public string $search = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $searchResults = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    // Pre-defined Specialty Templates & Bundles
    /**
     * @var array<string, array<string, mixed>>
     */
    public array $specialtyTemplates = [
        'Nephrology / CKD Specialist' => [
            'bundle' => [
                'name' => '⚡ 1-Click CKD Stage 4 Full Profile',
                'complaints' => "• Generalized body swelling (Edema) x 1 month\n• Decreased urine output (Oliguria)\n• Uncontrolled High Blood Pressure\n• Fatigue, nausea & loss of appetite",
                'findings' => 'Bilateral pitting pedal edema (++), Facial puffiness, Pallor (+), BP: 160/95 mmHg, Pulse: 80/min, Chest: bilateral basal crepitations',
                'diagnosis' => 'Chronic Kidney Disease (Stage 4), Hypertensive Nephrosclerosis, Renal Anemia',
                'investigations' => 'S. Creatinine & eGFR, Blood Urea Nitrogen (BUN), Serum Electrolytes (Na+, K+, Cl-, HCO3-), Urine R/M/E with Spot ACR, USG of KUB with PVR, CBC with ESR, iPTH, Serum Calcium & Phosphate',
                'advice' => "• Low protein diet (0.6 - 0.8 g/kg/day)\n• Fluid restriction to 1.5 Liters/day\n• Strict low salt diet (< 2 gm/day)\n• Avoid NSAIDs & nephrotoxic painkillers strictly\n• Avoid high potassium foods (banana, coconut water, green leaves)\n• Target BP < 130/80 mmHg\n• Follow-up after 15 days with S. Creatinine & Electrolytes report",
            ],
            'kits' => [
                [
                    'name' => '💊 CKD Core Drug Kit (4 Meds)',
                    'items' => [
                        ['type' => 'Tab.', 'name' => 'Ketosteril', 'generic' => 'Alpha-Ketoanalogues', 'strength' => '600mg', 'dosage' => '1+1+1', 'duration' => '1 month', 'instruction' => 'With meal (খাবারের সাথে)'],
                        ['type' => 'Tab.', 'name' => 'Sodibicarb', 'generic' => 'Sodium Bicarbonate', 'strength' => '500mg', 'dosage' => '1+0+1', 'duration' => '1 month', 'instruction' => 'After meal (খাবার পরে)'],
                        ['type' => 'Tab.', 'name' => 'Febux', 'generic' => 'Febuxostat', 'strength' => '40mg', 'dosage' => '0+0+1', 'duration' => '1 month', 'instruction' => 'After meal (খাবার পরে)'],
                        ['type' => 'Tab.', 'name' => 'Calcichew D', 'generic' => 'Calcium + Vit D3', 'strength' => '500mg+200IU', 'dosage' => '0+0+1', 'duration' => '1 month', 'instruction' => 'After meal (খাবার পরে)'],
                    ],
                ],
                [
                    'name' => '💊 CKD Edema Relief Kit',
                    'items' => [
                        ['type' => 'Tab.', 'name' => 'Torsid', 'generic' => 'Torsemide', 'strength' => '10mg', 'dosage' => '1+0+0', 'duration' => '14 days', 'instruction' => 'Morning only (সকালে)'],
                        ['type' => 'Tab.', 'name' => 'Amlopin', 'generic' => 'Amlodipine', 'strength' => '5mg', 'dosage' => '1+0+0', 'duration' => '1 month', 'instruction' => 'Morning (সকালে)'],
                    ],
                ],
            ],
            'complaints' => [
                'Generalized body swelling (Edema)',
                'Decreased urine output (Oliguria)',
                'Nausea & Loss of appetite',
                'Uncontrolled High BP',
                'Fatigue & generalized weakness',
                'Foamy / Frothy urine',
                'Shortness of breath on exertion',
                'Muscle cramps & generalized itching',
            ],
            'findings' => [
                'Bilateral pitting pedal edema (++)',
                'Pallor present (+)',
                'Facial puffiness',
                'BP: 160/95 mmHg',
                'Pulse: 80/min, regular',
                'Crepitations at lung bases',
                'Skin excoriation marks',
            ],
            'diagnosis' => [
                'Chronic Kidney Disease (Stage 4)',
                'Chronic Kidney Disease (Stage 3b)',
                'End-Stage Renal Disease (ESRD on HD)',
                'Diabetic Nephropathy',
                'Hypertensive Nephrosclerosis',
                'Chronic Glomerulonephritis',
                'Acute Kidney Injury (AKI) on CKD',
                'Nephrotic Syndrome',
            ],
            'investigations' => [
                'S. Creatinine & eGFR',
                'Blood Urea Nitrogen (BUN)',
                'Serum Electrolytes (Na+, K+, Cl-, HCO3-)',
                'Urine R/M/E with Spot ACR',
                'USG of KUB with PVR',
                'CBC with ESR',
                'Serum Calcium, Phosphate, Alkaline Phosphatase',
                'Intact PTH (iPTH)',
            ],
            'advice' => [
                'Low protein diet (0.6 - 0.8 g/kg/day)',
                'Fluid restriction to 1.5 Liters/day',
                'Strict low salt diet (< 2 gm/day)',
                'Avoid NSAIDs & nephrotoxic drugs strictly',
                'Maintain BP < 130/80 mmHg',
                'Avoid high potassium foods (banana, coconut water, tomato)',
                'Follow-up after 15 days with S. Creatinine & Electrolytes',
            ],
        ],
        'Cardiology' => [
            'bundle' => [
                'name' => '⚡ 1-Click IHD / Angina Profile',
                'complaints' => "• Central chest tightness on exertion x 2 weeks\n• Shortness of breath on walking\n• Palpitation & profuse sweating",
                'findings' => 'BP: 150/90 mmHg, Pulse: 84/min, Raised JVP, Ankle edema (+)',
                'diagnosis' => 'Ischaemic Heart Disease (IHD), Essential Hypertension (Grade 2), Dyslipidaemia',
                'investigations' => 'ECG (12-Lead), Echocardiography (2D & Color Doppler), Serum Troponin-I, Fasting Lipid Profile, Serum Electrolytes, Chest X-Ray',
                'advice' => "• Strictly low cholesterol and low salt diet\n• Stop smoking and tobacco strictly\n• Avoid heavy weight lifting & physical stress\n• Take emergency sublingual Nitroglycerin spray if acute chest pain occurs\n• Emergency hospital admission if pain lasts > 15 mins",
            ],
            'kits' => [
                [
                    'name' => '💊 Essential Heart & BP Kit',
                    'items' => [
                        ['type' => 'Tab.', 'name' => 'Amlopin', 'generic' => 'Amlodipine', 'strength' => '5mg', 'dosage' => '1+0+0', 'duration' => '1 month', 'instruction' => 'After breakfast (সকালে)'],
                        ['type' => 'Tab.', 'name' => 'Bizoran', 'generic' => 'Amlodipine + Olmesartan', 'strength' => '5mg/20mg', 'dosage' => '0+0+1', 'duration' => '1 month', 'instruction' => 'At bedtime (রাতে)'],
                        ['type' => 'Tab.', 'name' => 'Concor', 'generic' => 'Bisoprolol', 'strength' => '2.5mg', 'dosage' => '1+0+0', 'duration' => '1 month', 'instruction' => 'Morning (সকালে)'],
                    ],
                ],
            ],
            'complaints' => [
                'Chest tightness on exertion',
                'Palpitation & Sweating',
                'Breathlessness on lying down (Orthopnea)',
                'Swelling of both legs',
                'Dizziness & Occasional blackout',
            ],
            'findings' => [
                'BP: 150/90 mmHg',
                'Pulse: 88/min, irregular',
                'Raised JVP',
                'Bilateral ankle edema (+)',
            ],
            'diagnosis' => [
                'Ischaemic Heart Disease (IHD)',
                'Essential Hypertension (Grade 2)',
                'Congestive Cardiac Failure (CCF)',
                'Atrial Fibrillation (AF)',
            ],
            'investigations' => [
                'ECG (12-Lead)',
                'Echocardiography (2D Doppler)',
                'Lipid Profile (Fasting)',
                'Serum Troponin-I',
                'Chest X-Ray (P/A View)',
            ],
            'advice' => [
                'Low fat and low salt diet',
                'Strict avoidance of smoking & alcohol',
                'Regular 30 minutes light walking',
                'Emergency hospital admission if chest pain occurs',
            ],
        ],
        'General Medicine' => [
            'bundle' => [
                'name' => '⚡ 1-Click Acute Viral Fever Profile',
                'complaints' => "• High grade fever with chills x 3 days\n• Severe headache, generalized body & joint ache\n• Nausea and severe weakness",
                'findings' => 'Temp: 102°F, BP: 120/80 mmHg, Pulse: 96/min, Tongue coated, Throat congested, Abdomen soft non-tender',
                'diagnosis' => 'Acute Viral Syndrome / Dengue Fever to exclude',
                'investigations' => 'Complete Blood Count (CBC with Platelet & Hct), Blood for NS1 Antigen & Dengue IgM/IgG, Urine R/M/E',
                'advice' => "• Drink plenty of oral fluids, coconut water & ORS (at least 2.5 - 3 L/day)\n• Take tepid water sponging for high temperature\n• DO NOT take Aspirin or Diclofenac/NSAID painkillers\n• Strict bed rest\n• Report immediately if persistent vomiting or bleeding occurs",
            ],
            'kits' => [
                [
                    'name' => '💊 Fever & Bodyache Kit',
                    'items' => [
                        ['type' => 'Tab.', 'name' => 'Napa Extra', 'generic' => 'Paracetamol + Caffeine', 'strength' => '500+65mg', 'dosage' => '1+1+1', 'duration' => '3 days', 'instruction' => 'After meal (খাবার পরে)'],
                        ['type' => 'Cap.', 'name' => 'Protonix', 'generic' => 'Pantoprazole', 'strength' => '20mg', 'dosage' => '1+0+1', 'duration' => '7 days', 'instruction' => 'Before meal (খাবার আগে)'],
                        ['type' => 'Tab.', 'name' => 'Alarid', 'generic' => 'Cetirizine', 'strength' => '10mg', 'dosage' => '0+0+1', 'duration' => '5 days', 'instruction' => 'At bedtime (রাতে)'],
                    ],
                ],
            ],
            'complaints' => [
                'High grade fever with chills x 3 days',
                'Severe headache & generalized body ache',
                'Cough with expectoration',
                'Burning micturition',
                'Severe weakness and dizziness',
            ],
            'findings' => [
                'Temp: 102°F',
                'BP: 120/80 mmHg',
                'Pulse: 96/min',
                'Tongue coated, mild dehydration',
            ],
            'diagnosis' => [
                'Acute Viral Illness',
                'Enteric Fever (Typhoid)',
                'Urinary Tract Infection (UTI)',
                'Community Acquired Pneumonia',
            ],
            'investigations' => [
                'CBC with ESR & Platelet count',
                'Dengue NS1 Antigen',
                'Urine R/M/E & C/S',
                'RBS (Random Blood Sugar)',
            ],
            'advice' => [
                'Drink plenty of oral fluids and ORS',
                'Complete bed rest',
                'Tepid sponging for high fever',
                'Follow-up after 3 days with reports',
            ],
        ],
        'Gastroenterology' => [
            'bundle' => [
                'name' => '⚡ 1-Click GERD / PUD Profile',
                'complaints' => "• Severe burning pain in upper abdomen after meals\n• Acid regurgitation & chest burning (heartburn)\n• Bloating, belching & early fullness",
                'findings' => 'Epigastric tenderness present (+), Abdomen soft, Bowel sounds active',
                'diagnosis' => 'Gastroesophageal Reflux Disease (GERD), Peptic Ulcer Disease (PUD), Non-Ulcer Dyspepsia',
                'investigations' => 'Upper GI Endoscopy with CLO Test, USG of whole abdomen, H. Pylori Stool Antigen, Stool R/M/E',
                'advice' => "• Avoid spicy, oily, fried and acidic foods\n• Do not lie down within 2 hours after having meals\n• Avoid tea, coffee, smoking and fizzy drinks\n• Eat small frequent meals at regular times\n• Drink 2.5 - 3 Liters of purified water daily",
            ],
            'kits' => [
                [
                    'name' => '💊 Gastritis & Reflux Relief Kit',
                    'items' => [
                        ['type' => 'Cap.', 'name' => 'Protonix', 'generic' => 'Pantoprazole', 'strength' => '40mg', 'dosage' => '1+0+1', 'duration' => '14 days', 'instruction' => 'Before meal (খাবার আগে)'],
                        ['type' => 'Tab.', 'name' => 'Motigut', 'generic' => 'Domperidone', 'strength' => '10mg', 'dosage' => '1+0+1', 'duration' => '14 days', 'instruction' => '15 mins before meal (খাবার ১৫ মিনিট আগে)'],
                        ['type' => 'Syr.', 'name' => 'Antacid Plus', 'generic' => 'Mag Hydroxide + Al Hydroxide + Simethicone', 'strength' => '200ml', 'dosage' => '2 spoon 3 times', 'duration' => '7 days', 'instruction' => '1 hr after meal (খাবার ১ ঘণ্টা পর)'],
                    ],
                ],
            ],
            'complaints' => [
                'Burning epigastric pain after meals',
                'Acid regurgitation & heartburn',
                'Abdominal bloating & early satiety',
                'Nausea & Vomiting',
            ],
            'findings' => [
                'Epigastric tenderness present',
                'Abdominal distension (+)',
                'Bowel sounds active',
            ],
            'diagnosis' => [
                'Gastroesophageal Reflux Disease (GERD)',
                'Peptic Ulcer Disease (PUD)',
                'Irritable Bowel Syndrome (IBS)',
                'Non-Ulcer Dyspepsia',
            ],
            'investigations' => [
                'Upper GI Endoscopy with biopsy',
                'USG of whole abdomen',
                'H. Pylori Stool Antigen',
                'Liver Function Tests (SGPT, SGOT, Bilirubin)',
            ],
            'advice' => [
                'Avoid spicy, oily, fatty and sour foods',
                'Do not lie down within 2 hours after dinner',
                'Avoid tea, coffee, smoking and carbonated drinks',
                'Eat small frequent meals instead of heavy meals',
            ],
        ],
        'Diabetology / Endocrinology' => [
            'bundle' => [
                'name' => '⚡ 1-Click Uncontrolled T2DM Profile',
                'complaints' => "• Excessive thirst & frequent urination (Polyuria & Polydipsia)\n• Generalized weakness & weight loss\n• Burning sensation & numbness in feet (Neuropathy)",
                'findings' => 'BMI: 28.5 kg/m², BP: 140/90 mmHg, Peripheral pulses palpable, Reduced monofilament sensation in feet',
                'diagnosis' => 'Type 2 Diabetes Mellitus (Uncontrolled), Diabetic Peripheral Neuropathy',
                'investigations' => 'HbA1c, Fasting Blood Sugar & 2hr Post Prandial, Urine for Microalbuminuria, Serum Creatinine & eGFR, Fasting Lipid Profile',
                'advice' => "• Strict diabetic diet chart (avoid sugar, sweets, white bread & soft drinks)\n• Brisk walking for 45 minutes at least 5 days a week\n• Daily foot examination for any cut, redness or blister\n• Maintain home blood glucose monitoring record\n• Follow-up after 1 month with 3-point blood sugar chart",
            ],
            'kits' => [
                [
                    'name' => '💊 Diabetes Standard Starter Kit',
                    'items' => [
                        ['type' => 'Tab.', 'name' => 'Comet', 'generic' => 'Metformin', 'strength' => '500mg', 'dosage' => '1+0+1', 'duration' => '1 month', 'instruction' => 'With meal (খাবারের সাথে)'],
                        ['type' => 'Tab.', 'name' => 'Linaglip', 'generic' => 'Linagliptin', 'strength' => '5mg', 'dosage' => '0+1+0', 'duration' => '1 month', 'instruction' => 'After lunch (দুপুরে খাবার পর)'],
                        ['type' => 'Tab.', 'name' => 'Neobion', 'generic' => 'Vit B1+B6+B12', 'strength' => 'Standard', 'dosage' => '1+0+1', 'duration' => '1 month', 'instruction' => 'After meal (খাবার পরে)'],
                    ],
                ],
            ],
            'complaints' => [
                'Excessive thirst & increased urination at night',
                'Unexplained weight loss despite appetite',
                'Tingling & numbness in feet and hands',
                'Slow healing of wounds',
                'General fatigue & lethargy',
            ],
            'findings' => [
                'BMI: 28.5 kg/m²',
                'BP: 135/85 mmHg',
                'Diminished vibration sense in toes',
            ],
            'diagnosis' => [
                'Type 2 Diabetes Mellitus (Uncontrolled)',
                'Diabetic Peripheral Neuropathy',
                'Metabolic Syndrome',
            ],
            'investigations' => [
                'HbA1c',
                'Fasting Blood Glucose & 2hr Post Prandial',
                'Urine for Microalbuminuria',
                'Serum Creatinine & Lipid Profile',
            ],
            'advice' => [
                'Strict diabetic diet (avoid sweets, sugar, soft drinks)',
                '45 minutes regular walking daily',
                'Daily inspection of feet',
                'Maintain blood glucose chart before and after meals',
            ],
        ],
    ];

    public function mount(?int $prescriptionId = null): void
    {
        $chamber = Chamber::where('is_default', true)->first() ?: Chamber::first();
        if ($chamber) {
            $this->applyChamber($chamber);
        }

        $cloneId = $prescriptionId ?: request()->query('clone');
        if ($cloneId) {
            $cloneRx = Prescription::with(['patient', 'items', 'chamber'])->find($cloneId);
            if ($cloneRx) {
                if ($cloneRx->patient) {
                    $this->selectPatient($cloneRx->patient->id);
                }
                if ($cloneRx->chamber) {
                    $this->applyChamber($cloneRx->chamber);
                }
                $this->chief_complaints = $cloneRx->chief_complaints ?? '';
                $this->clinical_findings = $cloneRx->clinical_findings ?? '';
                $this->diagnosis = $cloneRx->diagnosis ?? '';
                $this->investigations = $cloneRx->investigations ?? '';
                $this->advice = $cloneRx->advice ?? '';

                $this->items = [];
                foreach ($cloneRx->items as $item) {
                    $this->items[] = [
                        'type' => $item->type ?? 'Tab.',
                        'name' => $item->medicine_name,
                        'generic' => $item->generic_name ?? '',
                        'strength' => $item->strength ?? '',
                        'dosage' => $item->dosage,
                        'duration' => $item->duration,
                        'instruction' => $item->instructions ?? 'After meal (খাবার পরে)',
                    ];
                }

                return;
            }
        }

        $this->addItem([
            'type' => 'Tab.',
            'name' => '',
            'generic' => '',
            'strength' => '',
            'dosage' => '1+0+1',
            'duration' => '7 days',
            'instruction' => 'After meal (খাবার পরে)',
        ]);
    }

    public function getChambersProperty(): Collection
    {
        return Chamber::orderBy('name')->get();
    }

    public function getTodayAppointmentsProperty(): Collection
    {
        if (! $this->selected_chamber_id) {
            return new Collection;
        }

        return Appointment::where('chamber_id', $this->selected_chamber_id)
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['waiting', 'with_doctor'])
            ->orderBy('serial_no')
            ->get();
    }

    public function selectChamber(int $id): void
    {
        $chamber = Chamber::findOrFail($id);
        $this->applyChamber($chamber);
    }

    protected function applyChamber(Chamber $chamber): void
    {
        $this->selected_chamber_id = $chamber->id;
        $this->doctor_name = $chamber->doctor_name;
        $this->doctor_qualification = $chamber->doctor_qualification;
        $this->doctor_designation = $chamber->doctor_designation;
        $this->hospital_affiliation = $chamber->hospital_affiliation ?? 'Bikrify Specialist Care';
        $this->chamber_name = $chamber->name;
        $this->chamber_address = $chamber->address ?? '';
        $this->room_number = $chamber->room_number ?? '';
        $this->chamber_phone = $chamber->phone ?? '';
        $this->secondary_phone = $chamber->secondary_phone ?? '';
        $this->whatsapp_number = $chamber->whatsapp_number ?? '';
        $this->chamber_visiting_hours = $chamber->visiting_hours ?? '';
        $this->specialty = $chamber->specialty ?: 'Nephrology / CKD Specialist';
    }

    // Call Patient from Today's Queue
    public function callQueuePatient(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        $this->current_appointment_id = $appointment->id;
        $appointment->update(['status' => 'with_doctor']);

        $this->patient_name = $appointment->patient_name;
        $this->patient_age = (string) $appointment->patient_age;
        $this->patient_gender = $appointment->patient_gender;
        $this->patient_phone = $appointment->patient_phone ?? '';

        // If patient already exists in DB, load their previous history
        if ($appointment->patient_id) {
            $this->selectPatient($appointment->patient_id);
        } elseif ($appointment->patient_phone) {
            $existing = Patient::where('phone', $appointment->patient_phone)->first();
            if ($existing) {
                $this->selectPatient($existing->id);
            } else {
                $this->is_existing_patient = false;
                $this->selected_patient_id = null;
            }
        }

        if (! empty($appointment->notes) && empty($this->chief_complaints)) {
            $this->chief_complaints = $appointment->notes;
        }
    }

    // Apply Full Specialty Bundle (1-Click Less Input)
    public function applySpecialtyBundle(): void
    {
        $tpl = $this->specialtyTemplates[$this->specialty]['bundle'] ?? null;
        if (! $tpl) {
            return;
        }

        $this->chief_complaints = $tpl['complaints'];
        $this->clinical_findings = $tpl['findings'];
        $this->diagnosis = $tpl['diagnosis'];
        $this->investigations = $tpl['investigations'];
        $this->advice = $tpl['advice'];
    }

    // Apply Specialty Drug Kit (1-Click Less Input)
    public function applyDrugKit(int $kitIndex): void
    {
        $kit = $this->specialtyTemplates[$this->specialty]['kits'][$kitIndex] ?? null;
        if (! $kit || empty($kit['items'])) {
            return;
        }

        // If only 1 empty row, clear it first
        if (count($this->items) === 1 && empty($this->items[0]['name'])) {
            $this->items = [];
        }

        foreach ($kit['items'] as $item) {
            $this->items[] = $item;
        }
    }

    // Insert Vitals into O/E
    public function insertVitals(): void
    {
        $parts = [];

        if ($this->vital_bp_systolic !== '' && $this->vital_bp_diastolic !== '') {
            $sys = (int) $this->vital_bp_systolic;
            $dia = (int) $this->vital_bp_diastolic;
            $bpStatus = ($sys >= 140 || $dia >= 90) ? ' (Stage 2 HTN)' : (($sys >= 130 || $dia >= 80) ? ' (Stage 1 HTN)' : ' (Normal)');
            $parts[] = "BP: {$sys}/{$dia} mmHg{$bpStatus}";
        }

        if ($this->vital_pulse !== '') {
            $parts[] = "Pulse: {$this->vital_pulse}/min";
        }

        if ($this->vital_weight !== '' && $this->vital_height_cm !== '') {
            $weight = (float) $this->vital_weight;
            $heightM = ((float) $this->vital_height_cm) / 100;
            if ($heightM > 0) {
                $bmi = round($weight / ($heightM * $heightM), 1);
                $bmiCat = $bmi >= 30 ? 'Obese' : ($bmi >= 25 ? 'Overweight' : ($bmi >= 18.5 ? 'Normal' : 'Underweight'));
                $parts[] = "Weight: {$weight} kg, Height: {$this->vital_height_cm} cm (BMI: {$bmi} {$bmiCat})";
            }
        } elseif ($this->vital_weight !== '') {
            $parts[] = "Weight: {$this->vital_weight} kg";
        }

        if ($this->vital_rbs !== '') {
            $parts[] = "RBS: {$this->vital_rbs} mmol/L";
        }

        if (! empty($parts)) {
            $vitalsString = implode(', ', $parts);
            if (empty($this->clinical_findings)) {
                $this->clinical_findings = $vitalsString;
            } else {
                $this->clinical_findings = $vitalsString."\n".$this->clinical_findings;
            }
        }
    }

    // Add Follow-Up to Advice in 1 Click
    public function addFollowUp(string $period): void
    {
        $days = match ($period) {
            '7 days' => 7,
            '15 days' => 15,
            '1 month' => 30,
            '2 months' => 60,
            '3 months' => 90,
            default => 15,
        };

        $returnDate = now()->addDays($days)->format('d M, Y');
        $followUpText = "• Follow-up after {$period} on {$returnDate} with latest investigation reports.";

        if (empty($this->advice)) {
            $this->advice = $followUpText;
        } else {
            $this->advice .= "\n".$followUpText;
        }
    }

    // Chamber Customization (Bikrify Onboarding Modal)
    public function openNewChamberModal(): void
    {
        $this->chamber_form_id = null;
        $this->chamber_form_name = '';
        $this->chamber_form_hospital_affiliation = '';
        $this->chamber_form_doctor_name = $this->doctor_name;
        $this->chamber_form_doctor_qualification = $this->doctor_qualification;
        $this->chamber_form_doctor_designation = $this->doctor_designation;
        $this->chamber_form_specialty = $this->specialty;
        $this->chamber_form_address = '';
        $this->chamber_form_room_number = '';
        $this->chamber_form_phone = '';
        $this->chamber_form_secondary_phone = '';
        $this->chamber_form_whatsapp_number = '';
        $this->chamber_form_visiting_hours = '';
        $this->showChamberModal = true;
    }

    public function openEditChamberModal(int $id): void
    {
        $chamber = Chamber::findOrFail($id);

        $this->chamber_form_id = $chamber->id;
        $this->chamber_form_name = $chamber->name;
        $this->chamber_form_hospital_affiliation = $chamber->hospital_affiliation ?? '';
        $this->chamber_form_doctor_name = $chamber->doctor_name;
        $this->chamber_form_doctor_qualification = $chamber->doctor_qualification;
        $this->chamber_form_doctor_designation = $chamber->doctor_designation;
        $this->chamber_form_specialty = $chamber->specialty;
        $this->chamber_form_address = $chamber->address ?? '';
        $this->chamber_form_room_number = $chamber->room_number ?? '';
        $this->chamber_form_phone = $chamber->phone ?? '';
        $this->chamber_form_secondary_phone = $chamber->secondary_phone ?? '';
        $this->chamber_form_whatsapp_number = $chamber->whatsapp_number ?? '';
        $this->chamber_form_visiting_hours = $chamber->visiting_hours ?? '';
        $this->showChamberModal = true;
    }

    public function closeChamberModal(): void
    {
        $this->showChamberModal = false;
    }

    public function saveChamber(): void
    {
        $this->validate([
            'chamber_form_name' => 'required|string|max:200',
            'chamber_form_doctor_name' => 'required|string|max:150',
            'chamber_form_specialty' => 'required|string|max:150',
        ]);

        $data = [
            'name' => $this->chamber_form_name,
            'hospital_affiliation' => $this->chamber_form_hospital_affiliation,
            'doctor_name' => $this->chamber_form_doctor_name,
            'doctor_qualification' => $this->chamber_form_doctor_qualification,
            'doctor_designation' => $this->chamber_form_doctor_designation,
            'specialty' => $this->chamber_form_specialty,
            'address' => $this->chamber_form_address,
            'room_number' => $this->chamber_form_room_number,
            'phone' => $this->chamber_form_phone,
            'secondary_phone' => $this->chamber_form_secondary_phone,
            'whatsapp_number' => $this->chamber_form_whatsapp_number,
            'visiting_hours' => $this->chamber_form_visiting_hours,
        ];

        if ($this->chamber_form_id) {
            $chamber = Chamber::findOrFail($this->chamber_form_id);
            $chamber->update($data);
            $this->applyChamber($chamber);
        } else {
            $chamber = Chamber::create($data);
            $this->applyChamber($chamber);
        }

        $this->showChamberModal = false;
    }

    // Patient Lookup & History
    public function updatedPatientSearch(): void
    {
        $term = trim($this->patient_search);
        if (strlen($term) >= 2) {
            $this->patientSearchResults = Patient::query()
                ->where('phone', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhere('patient_id', 'like', "%{$term}%")
                ->orderByDesc('id')
                ->limit(6)
                ->get(['id', 'patient_id', 'name', 'phone', 'age', 'gender'])
                ->toArray();
        } else {
            $this->patientSearchResults = [];
        }
    }

    public function selectPatient(int $id): void
    {
        $patient = Patient::with(['prescriptions.items'])->findOrFail($id);

        $this->selected_patient_id = $patient->id;
        $this->patient_name = $patient->name;
        $this->patient_age = (string) $patient->age;
        $this->patient_gender = $patient->gender;
        $this->patient_phone = $patient->phone ?? '';
        $this->is_existing_patient = true;

        $this->patientHistory = $patient->prescriptions
            ->sortByDesc('id')
            ->values()
            ->toArray();

        $this->patientSearchResults = [];
        $this->patient_search = '';
    }

    public function resetPatient(): void
    {
        $this->selected_patient_id = null;
        $this->is_existing_patient = false;
        $this->current_appointment_id = null;
        $this->patient_name = '';
        $this->patient_age = '';
        $this->patient_gender = 'Male';
        $this->patient_phone = '';
        $this->patientHistory = [];
        $this->patient_search = '';
        $this->patientSearchResults = [];
    }

    public function repeatPreviousPrescription(int $prescriptionId): void
    {
        $prescription = Prescription::with('items')->findOrFail($prescriptionId);
        if ($prescription->items->isEmpty()) {
            return;
        }

        $this->items = [];
        foreach ($prescription->items as $item) {
            $this->items[] = [
                'type' => $item->type ?? 'Tab.',
                'name' => $item->medicine_name,
                'generic' => $item->generic_name ?? '',
                'strength' => $item->strength ?? '',
                'dosage' => $item->dosage,
                'duration' => $item->duration,
                'instruction' => $item->instructions ?? 'After meal (খাবার পরে)',
            ];
        }

        if (empty($this->diagnosis) && $prescription->diagnosis) {
            $this->diagnosis = $prescription->diagnosis;
        }
        if (empty($this->advice) && $prescription->advice) {
            $this->advice = $prescription->advice;
        }

        $this->showHistoryModal = false;
    }

    // Medicine Search
    public function updatedSearch(): void
    {
        $term = trim($this->search);
        if (strlen($term) >= 2) {
            $tokens = preg_split('/\s+/', $term);
            $query = Medicine::query();

            foreach ($tokens as $token) {
                if ($token !== '') {
                    $query->where(function ($q) use ($token) {
                        $q->where('brand_name', 'like', "%{$token}%")
                            ->orWhere('generic_name', 'like', "%{$token}%");
                    });
                }
            }

            $this->searchResults = $query
                ->orderByRaw('CASE WHEN brand_name LIKE ? THEN 1 WHEN brand_name LIKE ? THEN 2 ELSE 3 END', [$term.'%', '%'.$term.'%'])
                ->limit(15)
                ->get(['id', 'dosage_form', 'brand_name', 'generic_name', 'strength', 'manufacturer'])
                ->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    /**
     * @param  array<string, mixed>|null  $item
     */
    public function addItem(?array $item = null): void
    {
        if ($item !== null && count($this->items) === 1 && empty($this->items[0]['name'])) {
            $this->items[0] = $item;
        } else {
            $this->items[] = $item ?? [
                'type' => 'Tab.',
                'name' => '',
                'generic' => '',
                'strength' => '',
                'dosage' => '1+0+1',
                'duration' => '7 days',
                'instruction' => 'After meal (খাবার পরে)',
            ];
        }

        $this->searchResults = [];
        $this->search = '';
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function appendQuickText(string $field, string $text): void
    {
        $current = trim((string) $this->{$field});

        if ($current === '') {
            $this->{$field} = $text;
        } else {
            if (in_array($field, ['diagnosis', 'investigations'])) {
                $this->{$field} = $current.', '.$text;
            } else {
                $this->{$field} = $current."\n• ".$text;
            }
        }
    }

    public function clearField(string $field): void
    {
        $this->{$field} = '';
    }

    public function saveAndPrint()
    {
        $this->validate([
            'patient_name' => 'required|string|max:150',
            'patient_age' => 'required|numeric|min:0|max:130',
            'patient_gender' => 'required|in:Male,Female,Other',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
        ]);

        if ($this->selected_patient_id) {
            $patient = Patient::findOrFail($this->selected_patient_id);
            $patient->update([
                'name' => $this->patient_name,
                'age' => (int) $this->patient_age,
                'gender' => $this->patient_gender,
                'phone' => $this->patient_phone ?: $patient->phone,
            ]);
        } else {
            $phone = trim($this->patient_phone);
            if ($phone !== '') {
                $patient = Patient::firstOrCreate(
                    ['phone' => $phone],
                    [
                        'patient_id' => 'PID-'.strtoupper(bin2hex(random_bytes(3))),
                        'name' => $this->patient_name,
                        'age' => (int) $this->patient_age,
                        'gender' => $this->patient_gender,
                    ]
                );
            } else {
                $patient = Patient::create([
                    'patient_id' => 'PID-'.strtoupper(bin2hex(random_bytes(3))),
                    'name' => $this->patient_name,
                    'age' => (int) $this->patient_age,
                    'gender' => $this->patient_gender,
                    'phone' => null,
                ]);
            }
        }

        $prescription = Prescription::create([
            'prescription_no' => 'RX-'.date('Ymd').'-'.rand(100, 999),
            'patient_id' => $patient->id,
            'chamber_id' => $this->selected_chamber_id,
            'doctor_name' => $this->doctor_name,
            'doctor_qualification' => $this->doctor_qualification,
            'doctor_designation' => $this->doctor_designation,
            'chamber_name' => $this->chamber_name,
            'chamber_address' => $this->chamber_address,
            'chamber_phone' => $this->chamber_phone,
            'chamber_visiting_hours' => $this->chamber_visiting_hours,
            'prescription_date' => now()->toDateString(),
            'chief_complaints' => $this->chief_complaints,
            'clinical_findings' => $this->clinical_findings,
            'diagnosis' => $this->diagnosis,
            'investigations' => $this->investigations,
            'advice' => $this->advice,
        ]);

        foreach ($this->items as $item) {
            $prescription->items()->create([
                'type' => $item['type'] ?? 'Tab.',
                'medicine_name' => $item['name'],
                'generic_name' => $item['generic'] ?? null,
                'strength' => $item['strength'] ?? null,
                'dosage' => $item['dosage'] ?? '1+0+1',
                'duration' => $item['duration'] ?? '7 days',
                'instructions' => $item['instruction'] ?? null,
            ]);
        }

        // If this prescription was created for a queue appointment, mark it completed!
        if ($this->current_appointment_id) {
            $appointment = Appointment::findOrFail($this->current_appointment_id);
            $appointment->update([
                'status' => 'completed',
                'prescription_id' => $prescription->id,
                'patient_id' => $patient->id,
            ]);
        }

        return redirect()->route('prescription.print', $prescription->id);
    }

    public function render(): View
    {
        return view('livewire.prescription-builder', [
            'allChambers' => $this->chambers,
            'todayAppointments' => $this->todayAppointments,
        ])->layout('layouts.app');
    }
}
