<?php

namespace App\Livewire;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PrescriptionBuilder extends Component
{
    public string $patient_name = '';

    public string $patient_age = '';

    public string $patient_gender = 'Male';

    public string $patient_phone = '';

    public string $chief_complaints = '';

    public string $clinical_findings = '';

    public string $diagnosis = '';

    public string $investigations = '';

    public string $advice = '';

    public string $search = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $searchResults = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public function mount(): void
    {
        // Initial blank medicine row
        $this->addItem([
            'type' => 'Tab.',
            'name' => '',
            'generic' => '',
            'strength' => '',
            'dosage' => '1+0+1',
            'duration' => '7 days',
            'instruction' => 'After meal',
        ]);
    }

    public function updatedSearch(): void
    {
        if (strlen($this->search) >= 2) {
            $this->searchResults = Medicine::query()
                ->where('brand_name', 'like', $this->search.'%')
                ->orWhere('generic_name', 'like', $this->search.'%')
                ->limit(8)
                ->get()
                ->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    /**
     * @param  array<string, mixed>  $med
     */
    public function selectMedicine(int $index, array $med): void
    {
        $this->items[$index]['type'] = $med['dosage_form'] ?? 'Tab.';
        $this->items[$index]['name'] = $med['brand_name'] ?? '';
        $this->items[$index]['generic'] = $med['generic_name'] ?? '';
        $this->items[$index]['strength'] = $med['strength'] ?? '';
        $this->searchResults = [];
        $this->search = '';
    }

    /**
     * @param  array<string, mixed>|null  $item
     */
    public function addItem(?array $item = null): void
    {
        $this->items[] = $item ?? [
            'type' => 'Tab.',
            'name' => '',
            'generic' => '',
            'strength' => '',
            'dosage' => '1+0+1',
            'duration' => '7 days',
            'instruction' => 'After meal',
        ];
        $this->searchResults = [];
        $this->search = '';
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
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

        $patient = Patient::firstOrCreate(
            ['phone' => $this->patient_phone ?: 'P-'.uniqid()],
            [
                'patient_id' => 'PID-'.strtoupper(bin2hex(random_bytes(3))),
                'name' => $this->patient_name,
                'age' => (int) $this->patient_age,
                'gender' => $this->patient_gender,
            ]
        );

        $prescription = Prescription::create([
            'prescription_no' => 'RX-'.date('Ymd').'-'.rand(100, 999),
            'patient_id' => $patient->id,
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
        return view('livewire.prescription-builder')->layout('layouts.app');
    }
}
