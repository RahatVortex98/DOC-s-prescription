<?php

namespace App\Livewire;

use App\Models\Chamber;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class PrescriptionBuilder extends Component
{
    // Patient Details
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

    // Doctor & Chamber Details
    public ?int $selected_chamber_id = null;

    public string $doctor_name = 'Prof. Dr. Mahbubur Rahman';

    public string $doctor_qualification = 'MBBS, FCPS, MD (Nephrology), BCS (Health)';

    public string $doctor_designation = 'Professor & Chief Consultant, Department of Nephrology';

    public string $chamber_name = 'Bikrify Digital Health Care';

    public string $chamber_address = 'House 45, Road 11, Block D, Banani, Dhaka';

    public string $chamber_phone = '+880 1711-223344';

    public string $chamber_visiting_hours = '4:00 PM - 8:00 PM (Sat - Thu)';

    public string $specialty = 'Nephrology / CKD Specialist';

    public bool $showChamberModal = false;

    // Chamber Management Form
    public ?int $chamber_form_id = null;

    public string $chamber_form_name = '';

    public string $chamber_form_doctor_name = '';

    public string $chamber_form_doctor_qualification = '';

    public string $chamber_form_doctor_designation = '';

    public string $chamber_form_specialty = '';

    public string $chamber_form_address = '';

    public string $chamber_form_phone = '';

    public string $chamber_form_visiting_hours = '';

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

    // Pre-defined Specialty Templates
    /**
     * @var array<string, array<string, list<string>>>
     */
    public array $specialtyTemplates = [
        'Nephrology / CKD Specialist' => [
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
                'Urine R/M/E with Protein / ACR',
                'USG of KUB with PVR',
                'CBC with ESR',
                'Serum Calcium, Phosphate, Alkaline Phosphatase',
                'Serum Albumin',
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
            'complaints' => [
                'Chest tightness / Discomfort on exertion',
                'Palpitation & Sweating',
                'Breathlessness on walking / lying down (Orthopnea)',
                'Swelling of both legs',
                'Dizziness & Occasional blackout',
            ],
            'findings' => [
                'BP: 150/90 mmHg',
                'Pulse: 88/min, irregular',
                'Raised JVP',
                'S3 Gallop rhythm audible',
                'Bilateral ankle edema (+)',
            ],
            'diagnosis' => [
                'Ischaemic Heart Disease (IHD)',
                'Essential Hypertension (Grade 2)',
                'Congestive Cardiac Failure (CCF)',
                'Non-ST Elevation Myocardial Infarction (NSTEMI)',
                'Atrial Fibrillation (AF)',
            ],
            'investigations' => [
                'ECG (12-Lead)',
                'Echocardiography (2D & Color Doppler)',
                'Serum Troponin-I',
                'Lipid Profile (Fasting)',
                'Serum Electrolytes',
                'Chest X-Ray (P/A View)',
            ],
            'advice' => [
                'Low fat and low salt diet',
                'Strict avoidance of smoking & alcohol',
                'Regular 30 minutes brisk walking daily',
                'Weight reduction & stress management',
                'Emergency hospital admission if chest pain occurs',
            ],
        ],
        'General Medicine' => [
            'complaints' => [
                'High grade fever with chills & rigor x 4 days',
                'Severe headache & generalized body ache',
                'Cough with expectoration',
                'Burning sensation during micturition',
                'Weakness and dizziness',
            ],
            'findings' => [
                'Temp: 102°F',
                'BP: 120/80 mmHg',
                'Tongue coated, Dehydration (+)',
                'Throat congested',
                'Abdomen soft, non-tender',
            ],
            'diagnosis' => [
                'Acute Viral Illness',
                'Enteric Fever (Typhoid)',
                'Urinary Tract Infection (UTI)',
                'Community Acquired Pneumonia',
                'Acute Gastroenteritis',
            ],
            'investigations' => [
                'CBC with ESR',
                'Urine R/M/E and Culture & Sensitivity',
                'Widal Test / Typhidot IgM',
                'Blood for NS1 Antigen & Dengue Antibody',
                'RBS (Random Blood Sugar)',
            ],
            'advice' => [
                'Drink plenty of oral fluids and ORS',
                'Complete bed rest',
                'Take tepid sponge sponging for high fever',
                'Avoid outside food and street water',
                'Follow-up after 5 days with reports',
            ],
        ],
        'Gastroenterology' => [
            'complaints' => [
                'Burning epigastric pain after meals',
                'Frequent acid regurgitation & heartburn',
                'Abdominal bloating & early satiety',
                'Loose watery motions x 2 days',
                'Nausea & Vomiting',
            ],
            'findings' => [
                'Epigastric tenderness present',
                'Abdominal distension (+)',
                'Bowel sounds hyperactive',
                'Dehydration mild',
            ],
            'diagnosis' => [
                'Gastroesophageal Reflux Disease (GERD)',
                'Peptic Ulcer Disease (PUD)',
                'Irritable Bowel Syndrome (IBS)',
                'Acute Gastroenteritis',
                'Non-Ulcer Dyspepsia',
            ],
            'investigations' => [
                'Upper GI Endoscopy with biopsy',
                'USG of whole abdomen',
                'H. Pylori Stool Antigen',
                'Stool R/M/E & Occult Blood Test',
                'Liver Function Tests (SGPT, SGOT, Bilirubin)',
            ],
            'advice' => [
                'Avoid spicy, oily, fatty and sour foods',
                'Do not lie down within 2 hours after dinner',
                'Avoid tea, coffee, smoking and chocolate',
                'Eat small frequent meals instead of heavy meals',
                'Drink at least 2.5 - 3 Liters of purified water daily',
            ],
        ],
        'Diabetology / Endocrinology' => [
            'complaints' => [
                'Excessive thirst & increased urination (especially at night)',
                'Unexplained weight loss despite normal appetite',
                'Tingling & numbness in feet and fingers',
                'Slow healing of wounds',
                'General fatigue & lethargy',
            ],
            'findings' => [
                'BMI: 28.4 kg/m² (Overweight)',
                'BP: 135/85 mmHg',
                'Peripheral pulses palpable',
                'Diminished vibration sense in toes',
            ],
            'diagnosis' => [
                'Type 2 Diabetes Mellitus (Uncontrolled)',
                'Diabetic Peripheral Neuropathy',
                'Impaired Fasting Glucose (Prediabetes)',
                'Metabolic Syndrome',
            ],
            'investigations' => [
                'HbA1c',
                'Fasting Blood Glucose & 2hr ABF',
                'Urine for Microalbuminuria',
                'Serum Creatinine & Lipid Profile',
                'Fundoscopy (Eye Examination)',
            ],
            'advice' => [
                'Strict diabetic diet plan (avoid sweets, sugar, soft drinks)',
                '45 minutes regular morning walking daily',
                'Daily inspection of feet for any cut or blister',
                'Maintain blood glucose chart before and after meals',
                'Follow-up after 1 month with 3-point glucose profile',
            ],
        ],
    ];

    public function mount(): void
    {
        // Load default or first chamber
        $chamber = Chamber::where('is_default', true)->first() ?: Chamber::first();
        if ($chamber) {
            $this->applyChamber($chamber);
        }

        // Initial blank medicine row
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

    public function selectChamber(int $id): void
    {
        $chamber = Chamber::find($id);
        if ($chamber) {
            $this->applyChamber($chamber);
        }
    }

    protected function applyChamber(Chamber $chamber): void
    {
        $this->selected_chamber_id = $chamber->id;
        $this->doctor_name = $chamber->doctor_name;
        $this->doctor_qualification = $chamber->doctor_qualification;
        $this->doctor_designation = $chamber->doctor_designation;
        $this->chamber_name = $chamber->name;
        $this->chamber_address = $chamber->address ?? '';
        $this->chamber_phone = $chamber->phone ?? '';
        $this->chamber_visiting_hours = $chamber->visiting_hours ?? '';
        $this->specialty = $chamber->specialty ?: 'Nephrology / CKD Specialist';
    }

    public function openNewChamberModal(): void
    {
        $this->chamber_form_id = null;
        $this->chamber_form_name = '';
        $this->chamber_form_doctor_name = $this->doctor_name;
        $this->chamber_form_doctor_qualification = $this->doctor_qualification;
        $this->chamber_form_doctor_designation = $this->doctor_designation;
        $this->chamber_form_specialty = $this->specialty;
        $this->chamber_form_address = '';
        $this->chamber_form_phone = '';
        $this->chamber_form_visiting_hours = '';
        $this->showChamberModal = true;
    }

    public function openEditChamberModal(int $id): void
    {
        $chamber = Chamber::find($id);
        if (! $chamber) {
            return;
        }

        $this->chamber_form_id = $chamber->id;
        $this->chamber_form_name = $chamber->name;
        $this->chamber_form_doctor_name = $chamber->doctor_name;
        $this->chamber_form_doctor_qualification = $chamber->doctor_qualification;
        $this->chamber_form_doctor_designation = $chamber->doctor_designation;
        $this->chamber_form_specialty = $chamber->specialty;
        $this->chamber_form_address = $chamber->address ?? '';
        $this->chamber_form_phone = $chamber->phone ?? '';
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
            'doctor_name' => $this->chamber_form_doctor_name,
            'doctor_qualification' => $this->chamber_form_doctor_qualification,
            'doctor_designation' => $this->chamber_form_doctor_designation,
            'specialty' => $this->chamber_form_specialty,
            'address' => $this->chamber_form_address,
            'phone' => $this->chamber_form_phone,
            'visiting_hours' => $this->chamber_form_visiting_hours,
        ];

        if ($this->chamber_form_id) {
            $chamber = Chamber::find($this->chamber_form_id);
            if ($chamber) {
                $chamber->update($data);
                $this->applyChamber($chamber);
            }
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
        $patient = Patient::with(['prescriptions.items'])->find($id);
        if (! $patient) {
            return;
        }

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
        $prescription = Prescription::with('items')->find($prescriptionId);
        if (! $prescription || $prescription->items->isEmpty()) {
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

        // Also suggest diagnosis and complaints if blank
        if (empty($this->diagnosis) && $prescription->diagnosis) {
            $this->diagnosis = $prescription->diagnosis;
        }
        if (empty($this->advice) && $prescription->advice) {
            $this->advice = $prescription->advice;
        }

        $this->showHistoryModal = false;
    }

    // Medicine Search & Management
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

    // Quick Specialty Badge Append
    public function appendQuickText(string $field, string $text): void
    {
        $current = trim((string) $this->{$field});

        if ($current === '') {
            $this->{$field} = $text;
        } else {
            // Append with comma or newline based on field
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
            $patient = Patient::find($this->selected_patient_id);
            if ($patient) {
                $patient->update([
                    'name' => $this->patient_name,
                    'age' => (int) $this->patient_age,
                    'gender' => $this->patient_gender,
                    'phone' => $this->patient_phone ?: $patient->phone,
                ]);
            }
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

        return redirect()->route('prescription.print', $prescription->id);
    }

    public function render(): View
    {
        return view('livewire.prescription-builder', [
            'allChambers' => $this->chambers,
        ])->layout('layouts.app');
    }
}
