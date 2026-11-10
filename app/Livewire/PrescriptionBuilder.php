<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Chamber;
use App\Models\DrugKit;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public string $patient_allergies = '';

    // Active Appointment Tracking
    #[Locked]
    public ?int $current_appointment_id = null;

    // Doctor & Chamber Details (Driven by tenant & chamber profiles)
    #[Locked]
    public ?int $selected_chamber_id = null;

    public string $doctor_name = '';

    public string $doctor_qualification = '';

    public string $doctor_designation = '';

    public string $bmdc_no = '';

    public string $hospital_affiliation = '';

    public string $chamber_name = '';

    public string $chamber_address = '';

    public string $room_number = '';

    public string $chamber_phone = '';

    public string $secondary_phone = '';

    public string $whatsapp_number = '';

    public string $chamber_visiting_hours = '';

    public string $specialty = 'General Medicine';

    public bool $showChamberModal = false;

    public bool $hasNoChamberSetup = false;

    // Chamber Form Details
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

    public function mount(?int $prescriptionId = null): void
    {
        $tenant = auth()->user()?->tenant;

        if ($tenant) {
            $this->doctor_name = $tenant->doctor_name ?? '';
            $this->doctor_qualification = $tenant->doctor_qualification ?? '';
            $this->doctor_designation = $tenant->doctor_designation ?? '';
            $this->bmdc_no = $tenant->bmdc_no ?? '';
            $this->specialty = $tenant->specialty ?: 'General Medicine';
        }

        $chamber = Chamber::where('is_default', true)->first() ?: Chamber::first();
        if (! $chamber) {
            $tenant = auth()->user()?->tenant;
            if ($tenant) {
                $chamber = Chamber::withoutGlobalScope('tenant')->create([
                    'tenant_id' => $tenant->id,
                    'name' => 'Main Consultation Chamber',
                    'doctor_name' => $tenant->doctor_name ?: 'Doctor',
                    'doctor_qualification' => $tenant->doctor_qualification ?: 'MBBS',
                    'doctor_designation' => $tenant->doctor_designation ?: 'Consultant',
                    'specialty' => $tenant->specialty ?: 'General Medicine',
                    'is_default' => true,
                ]);
            }
        }

        if ($chamber) {
            $this->applyChamber($chamber);
        }

        $cloneId = $prescriptionId ?: request()->query('clone');
        if ($cloneId) {
            $cloneRx = Prescription::with(['patient', 'items', 'chamber'])->find($cloneId);
            if ($cloneRx && ! $cloneRx->isVoided()) {
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

    public function getTemplatesProperty(): Collection
    {
        $query = PrescriptionTemplate::query();
        if (! empty($this->specialty)) {
            $query->where(function ($q) {
                $q->where('specialty', $this->specialty)
                    ->orWhereNull('specialty');
            });
        }

        return $query->get();
    }

    public function getDrugKitsProperty(): Collection
    {
        $query = DrugKit::query();
        if (! empty($this->specialty)) {
            $query->where(function ($q) {
                $q->where('specialty', $this->specialty)
                    ->orWhereNull('specialty');
            });
        }

        return $query->get();
    }

    /**
     * @return array<string, list<string>>
     */
    public function getSpecialtyChipsProperty(): array
    {
        $templates = $this->templates;

        $complaints = [];
        $findings = [];
        $diagnoses = [];
        $investigations = [];
        $advices = [];

        foreach ($templates as $t) {
            if ($t->chief_complaints) {
                foreach (preg_split('/[,\n•]+/', $t->chief_complaints) as $c) {
                    $c = trim($c);
                    if (strlen($c) > 2) {
                        $complaints[$c] = $c;
                    }
                }
            }
            if ($t->clinical_findings) {
                foreach (preg_split('/[,\n•]+/', $t->clinical_findings) as $f) {
                    $f = trim($f);
                    if (strlen($f) > 2) {
                        $findings[$f] = $f;
                    }
                }
            }
            if ($t->diagnosis) {
                foreach (preg_split('/[,\n•]+/', $t->diagnosis) as $d) {
                    $d = trim($d);
                    if (strlen($d) > 2) {
                        $diagnoses[$d] = $d;
                    }
                }
            }
            if ($t->investigations) {
                foreach (preg_split('/[,\n•]+/', $t->investigations) as $i) {
                    $i = trim($i);
                    if (strlen($i) > 2) {
                        $investigations[$i] = $i;
                    }
                }
            }
            if ($t->advice) {
                foreach (preg_split('/[,\n•]+/', $t->advice) as $a) {
                    $a = trim($a);
                    if (strlen($a) > 2) {
                        $advices[$a] = $a;
                    }
                }
            }
        }

        return [
            'complaints' => array_values(array_slice($complaints, 0, 8)),
            'findings' => array_values(array_slice($findings, 0, 8)),
            'diagnosis' => array_values(array_slice($diagnoses, 0, 8)),
            'investigations' => array_values(array_slice($investigations, 0, 8)),
            'advice' => array_values(array_slice($advices, 0, 8)),
        ];
    }

    public function selectChamber(int $id): void
    {
        $chamber = Chamber::findOrFail($id);
        $this->applyChamber($chamber);
    }

    protected function applyChamber(Chamber $chamber): void
    {
        $tenant = auth()->user()?->tenant;

        $this->selected_chamber_id = $chamber->id;
        $this->doctor_name = $tenant?->doctor_name ?: $chamber->doctor_name;
        $this->doctor_qualification = $tenant?->doctor_qualification ?: ($chamber->doctor_qualification ?? '');
        $this->doctor_designation = $tenant?->doctor_designation ?: ($chamber->doctor_designation ?? '');
        $this->bmdc_no = $tenant?->bmdc_no ?: '';
        $this->specialty = $tenant?->specialty ?: ($chamber->specialty ?: 'General Medicine');

        $this->hospital_affiliation = $chamber->hospital_affiliation ?? '';
        $this->chamber_name = $chamber->name;
        $this->chamber_address = $chamber->address ?? '';
        $this->room_number = $chamber->room_number ?? '';
        $this->chamber_phone = $chamber->phone ?? '';
        $this->secondary_phone = $chamber->secondary_phone ?? '';
        $this->whatsapp_number = $chamber->whatsapp_number ?? '';
        $this->chamber_visiting_hours = $chamber->visiting_hours ?? '';
        $this->hasNoChamberSetup = false;
    }

    public function callQueuePatient(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        $this->current_appointment_id = $appointment->id;
        $appointment->update(['status' => 'with_doctor']);

        $this->patient_name = $appointment->patient_name;
        $this->patient_age = (string) $appointment->patient_age;
        $this->patient_gender = $appointment->patient_gender;
        $this->patient_phone = $appointment->patient_phone ?? '';

        if ($appointment->patient_id) {
            $this->selectPatient($appointment->patient_id);
        } elseif ($appointment->patient_phone) {
            $existing = Patient::where('phone', $appointment->patient_phone)->first();
            if ($existing) {
                $this->selectPatient($existing->id);
            } else {
                $this->selected_patient_id = null;
                $this->is_existing_patient = false;
            }
        }

        if (! empty($appointment->notes) && empty($this->chief_complaints)) {
            $this->chief_complaints = $appointment->notes;
        }
    }

    public function applySpecialtyBundle(?int $templateId = null): void
    {
        $template = $templateId
            ? PrescriptionTemplate::findOrFail($templateId)
            : (PrescriptionTemplate::where('specialty', $this->specialty)->first()
                ?? PrescriptionTemplate::first());

        if (! $template) {
            return;
        }

        $this->chief_complaints = $template->chief_complaints ?? '';
        $this->clinical_findings = $template->clinical_findings ?? '';
        $this->diagnosis = $template->diagnosis ?? '';
        $this->investigations = $template->investigations ?? '';
        $this->advice = $template->advice ?? '';
    }

    public function applyDrugKit(int $kitIdOrIndex): void
    {
        $kit = DrugKit::find($kitIdOrIndex)
            ?? DrugKit::where('specialty', $this->specialty)->skip($kitIdOrIndex)->first()
            ?? DrugKit::skip($kitIdOrIndex)->first();

        if (! $kit || empty($kit->items)) {
            return;
        }

        if (count($this->items) === 1 && empty($this->items[0]['name'])) {
            $this->items = [];
        }

        foreach ($kit->items as $item) {
            $this->items[] = $item;
        }
    }

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
                $parts[] = "BMI: {$bmi}";
            }
        }

        if ($this->vital_rbs !== '') {
            $parts[] = "RBS: {$this->vital_rbs} mmol/L";
        }

        if (! empty($parts)) {
            $vitalString = implode(', ', $parts);
            if (trim($this->clinical_findings) === '') {
                $this->clinical_findings = $vitalString;
            } else {
                $this->clinical_findings .= "\n".$vitalString;
            }
        }

        $this->vital_bp_systolic = '';
        $this->vital_bp_diastolic = '';
        $this->vital_pulse = '';
        $this->vital_rbs = '';
    }

    public function openNewChamberModal(): void
    {
        $tenant = auth()->user()?->tenant;

        $this->chamber_form_id = null;
        $this->chamber_form_name = '';
        $this->chamber_form_hospital_affiliation = '';
        $this->chamber_form_doctor_name = $tenant?->doctor_name ?: $this->doctor_name;
        $this->chamber_form_doctor_qualification = $tenant?->doctor_qualification ?: $this->doctor_qualification;
        $this->chamber_form_doctor_designation = $tenant?->doctor_designation ?: $this->doctor_designation;
        $this->chamber_form_specialty = $tenant?->specialty ?: $this->specialty;
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
        ]);

        $tenant = auth()->user()?->tenant;

        $data = [
            'name' => $this->chamber_form_name,
            'hospital_affiliation' => $this->chamber_form_hospital_affiliation,
            // Verified doctor credentials permanently locked to tenant
            'doctor_name' => $tenant?->doctor_name ?: ($this->chamber_form_doctor_name ?: $this->doctor_name),
            'doctor_qualification' => $tenant?->doctor_qualification ?: $this->chamber_form_doctor_qualification,
            'doctor_designation' => $tenant?->doctor_designation ?: $this->chamber_form_doctor_designation,
            'specialty' => $tenant?->specialty ?: ($this->chamber_form_specialty ?: $this->specialty),
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

        $this->hasNoChamberSetup = false;
        $this->showChamberModal = false;
    }

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
        $this->patient_allergies = $patient->allergies ?? '';
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
        $this->patient_allergies = '';
        $this->patientHistory = [];
        $this->patient_search = '';
        $this->patientSearchResults = [];
    }

    public function repeatPreviousPrescription(int $prescriptionId): void
    {
        $prescription = Prescription::with('items')->findOrFail($prescriptionId);
        if ($prescription->isVoided() || $prescription->items->isEmpty()) {
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

        $this->chief_complaints = $prescription->chief_complaints ?? '';
        $this->clinical_findings = $prescription->clinical_findings ?? '';
        $this->diagnosis = $prescription->diagnosis ?? '';
        $this->investigations = $prescription->investigations ?? '';
        $this->advice = $prescription->advice ?? '';

        $this->showHistoryModal = false;
    }

    public function updatedSearch(): void
    {
        $term = trim($this->search);
        if (strlen($term) >= 2) {
            $tokens = preg_split('/\s+/', $term);
            $query = Medicine::query();

            foreach ($tokens as $token) {
                if ($token !== '') {
                    $query->where(function ($sub) use ($token) {
                        $sub->where('brand_name', 'like', "%{$token}%")
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

    public function addVoiceMedicine(string $name, string $dosage = '1+0+1', string $duration = '7 days', string $instruction = 'After meal (খাবার পরে)', string $type = 'Tab.'): void
    {
        $name = trim($name);
        if (empty($name)) {
            return;
        }

        if (count($this->items) === 1 && empty($this->items[0]['name'])) {
            $this->items[0] = [
                'type' => $type,
                'name' => $name,
                'generic' => '',
                'strength' => '',
                'dosage' => $dosage,
                'duration' => $duration,
                'instruction' => $instruction,
            ];

            return;
        }

        $this->items[] = [
            'type' => $type,
            'name' => $name,
            'generic' => '',
            'strength' => '',
            'dosage' => $dosage,
            'duration' => $duration,
            'instruction' => $instruction,
        ];
    }

    public function clearField(string $field): void
    {
        $this->{$field} = '';
    }

    public function addFollowUp(string $duration): void
    {
        $followUpText = "Next visit / Follow-up: after {$duration} (or with investigation reports).";
        $current = trim((string) $this->advice);

        if ($current === '') {
            $this->advice = $followUpText;

            return;
        }

        // If a follow-up line already exists in advice, replace it cleanly to avoid duplicates
        if (preg_match('/(Next visit|Follow-up|পরবর্তী সাক্ষাৎ)[^\n]*/iu', $current)) {
            $this->advice = (string) preg_replace('/(Next visit|Follow-up|পরবর্তী সাক্ষাৎ)[^\n]*/iu', $followUpText, $current);
        } else {
            $this->advice = $current."\n• ".$followUpText;
        }
    }

    public function saveAndPrint()
    {
        if (! $this->selected_chamber_id) {
            $chamber = Chamber::where('is_default', true)->first() ?: Chamber::first();
            if ($chamber) {
                $this->applyChamber($chamber);
            }
        }

        $chamber = Chamber::find($this->selected_chamber_id)
            ?? Chamber::where('is_default', true)->first()
            ?? Chamber::first();

        if (! $chamber) {
            $tenant = auth()->user()?->tenant;
            $chamber = Chamber::withoutGlobalScope('tenant')->create([
                'tenant_id' => $tenant?->id,
                'name' => 'Main Consultation Chamber',
                'doctor_name' => $tenant?->doctor_name ?: 'Doctor',
                'doctor_qualification' => $tenant?->doctor_qualification ?: 'MBBS',
                'doctor_designation' => $tenant?->doctor_designation ?: 'Consultant',
                'specialty' => $tenant?->specialty ?: 'General Medicine',
                'is_default' => true,
            ]);
            $this->applyChamber($chamber);
        }

        $this->validate([
            'patient_name' => 'required|string|max:150',
            'patient_age' => 'required|numeric|min:0|max:130',
            'patient_gender' => 'required|in:Male,Female,Other',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
        ]);

        $trimmedName = trim($this->patient_name);
        $phone = trim((string) $this->patient_phone);

        if ($this->selected_patient_id) {
            $patient = Patient::findOrFail($this->selected_patient_id);
            $patient->update([
                'name' => $trimmedName,
                'age' => (int) $this->patient_age,
                'gender' => $this->patient_gender,
                'phone' => $phone ?: $patient->phone,
                'allergies' => $this->patient_allergies ?: $patient->allergies,
            ]);
        } else {
            if ($phone !== '') {
                $patient = Patient::where('phone', $phone)
                    ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($trimmedName)])
                    ->first();

                if (! $patient) {
                    $patient = Patient::create([
                        'patient_id' => 'PID-'.strtoupper(bin2hex(random_bytes(3))),
                        'name' => $trimmedName,
                        'age' => (int) $this->patient_age,
                        'gender' => $this->patient_gender,
                        'phone' => $phone,
                        'allergies' => $this->patient_allergies ?: null,
                    ]);
                } else {
                    $patient->update([
                        'age' => (int) $this->patient_age,
                        'gender' => $this->patient_gender,
                        'allergies' => $this->patient_allergies ?: $patient->allergies,
                    ]);
                }
            } else {
                $patient = Patient::create([
                    'patient_id' => 'PID-'.strtoupper(bin2hex(random_bytes(3))),
                    'name' => $trimmedName,
                    'age' => (int) $this->patient_age,
                    'gender' => $this->patient_gender,
                    'phone' => null,
                    'allergies' => $this->patient_allergies ?: null,
                ]);
            }
        }

        // Collision-safe RX number with retry
        $prescription = null;
        $attempts = 0;
        while (! $prescription && $attempts < 10) {
            $attempts++;
            try {
                $candidateRxNo = 'RX-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
                $prescription = DB::transaction(function () use ($candidateRxNo, $patient) {
                    $rx = Prescription::create([
                        'prescription_no' => $candidateRxNo,
                        'patient_id' => $patient->id,
                        'chamber_id' => $this->selected_chamber_id,
                        'doctor_name' => $this->doctor_name,
                        'doctor_qualification' => $this->doctor_qualification,
                        'doctor_designation' => $this->doctor_designation,
                        'doctor_bmdc_no' => $this->bmdc_no ?: null,
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
                        if (! empty($item['name'])) {
                            $rx->items()->create([
                                'type' => $item['type'] ?? 'Tab.',
                                'medicine_name' => $item['name'],
                                'generic_name' => $item['generic'] ?? null,
                                'strength' => $item['strength'] ?? null,
                                'dosage' => $item['dosage'] ?? '1+0+1',
                                'duration' => $item['duration'] ?? '7 days',
                                'instructions' => $item['instruction'] ?? null,
                            ]);
                        }
                    }

                    return $rx;
                });
            } catch (UniqueConstraintViolationException $e) {
                $prescription = null;
            }
        }

        if (! $prescription) {
            throw new \RuntimeException('Failed to generate unique prescription serial number.');
        }

        AuditLog::record('prescription.created', $prescription, [
            'rx_no' => $prescription->prescription_no,
            'patient' => $patient->name,
        ]);

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
            'todayQueue' => $this->todayAppointments,
            'templates' => $this->templates,
            'drugKits' => $this->drugKits,
            'specialtyChips' => $this->specialtyChips,
        ]);
    }
}
