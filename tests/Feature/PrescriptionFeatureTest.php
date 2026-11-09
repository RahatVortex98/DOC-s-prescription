<?php

namespace Tests\Feature;

use App\Livewire\PrescriptionBuilder;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class PrescriptionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescription_builder_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Bikrify Prescription Studio');
    }

    public function test_medicine_search_returns_matching_results(): void
    {
        Medicine::create([
            'brand_name' => 'Napa Extend',
            'generic_name' => 'Paracetamol',
            'dosage_form' => 'Tab.',
            'strength' => '665mg',
            'manufacturer' => 'Beximco Pharmaceuticals Ltd.',
        ]);

        Livewire::test(PrescriptionBuilder::class)
            ->set('search', 'Nap')
            ->assertSet('searchResults', function ($results) {
                return count($results) === 1 && $results[0]['brand_name'] === 'Napa Extend';
            });
    }

    public function test_user_can_add_and_remove_medicine_rows(): void
    {
        Livewire::test(PrescriptionBuilder::class)
            ->assertCount('items', 1)
            ->call('addItem', [
                'type' => 'Inj.',
                'name' => 'Ceftri',
                'generic' => 'Ceftriaxone',
                'strength' => '500mg',
                'dosage' => '1+0+0',
                'duration' => '3 days',
                'instruction' => 'After meal',
            ])
            ->assertCount('items', 2)
            ->call('removeItem', 0)
            ->assertCount('items', 1)
            ->assertSet('items.0.name', 'Ceftri');
    }

    public function test_validation_prevents_saving_empty_prescription(): void
    {
        Livewire::test(PrescriptionBuilder::class)
            ->call('saveAndPrint')
            ->assertHasErrors(['patient_name', 'patient_age']);
    }

    public function test_save_and_print_creates_patient_and_prescription_and_redirects(): void
    {
        Livewire::test(PrescriptionBuilder::class)
            ->set('patient_name', 'Rahim Ahmed')
            ->set('patient_age', '45')
            ->set('patient_gender', 'Male')
            ->set('patient_phone', '01711223344')
            ->set('chief_complaints', 'High fever and headache')
            ->set('clinical_findings', 'BP: 120/80')
            ->set('diagnosis', 'Viral Fever')
            ->set('investigations', 'CBC, Urine R/E')
            ->set('advice', 'Drink plenty of fluids')
            ->set('items.0.name', 'Napa Extend')
            ->set('items.0.type', 'Tab.')
            ->set('items.0.generic', 'Paracetamol')
            ->set('items.0.strength', '665mg')
            ->set('items.0.dosage', '1+0+1')
            ->set('items.0.duration', '5 days')
            ->set('items.0.instruction', 'After meal')
            ->call('saveAndPrint')
            ->assertRedirect();

        $this->assertDatabaseHas('patients', [
            'name' => 'Rahim Ahmed',
            'phone' => '01711223344',
            'age' => 45,
            'gender' => 'Male',
        ]);

        $patient = Patient::where('phone', '01711223344')->first();
        $this->assertNotNull($patient);

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'diagnosis' => 'Viral Fever',
        ]);

        $prescription = Prescription::where('patient_id', $patient->id)->first();
        $this->assertNotNull($prescription);

        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Napa Extend',
            'dosage' => '1+0+1',
        ]);
    }

    public function test_prescription_print_view_displays_information(): void
    {
        $patient = Patient::create([
            'patient_id' => 'PID-123456',
            'name' => 'Salma Khatun',
            'phone' => '01899887766',
            'age' => 32,
            'gender' => 'Female',
        ]);

        $prescription = Prescription::create([
            'prescription_no' => 'RX-20261109-999',
            'patient_id' => $patient->id,
            'doctor_name' => 'Dr. Mahbubur Rahman',
            'doctor_qualification' => 'MBBS, BCS (Health), MS (Surgery)',
            'chamber_name' => 'Bikrify Digital Health Care',
            'prescription_date' => now()->toDateString(),
            'chief_complaints' => 'Stomach pain',
            'clinical_findings' => 'Epigastric tenderness',
            'diagnosis' => 'Peptic Ulcer Disease',
            'investigations' => 'Endoscopy',
            'advice' => 'Avoid spicy food',
        ]);

        $prescription->items()->create([
            'type' => 'Cap.',
            'medicine_name' => 'Seclo',
            'generic_name' => 'Omeprazole',
            'strength' => '20mg',
            'dosage' => '1+0+1',
            'duration' => '14 days',
            'instructions' => 'Before meal',
        ]);

        $response = $this->get(route('prescription.print', $prescription->id));

        $response->assertStatus(200);
        $response->assertSee('Salma Khatun');
        $response->assertSee('RX-20261109-999');
        $response->assertSee('Seclo');
        $response->assertSee('Omeprazole');
    }

    public function test_prescription_pdf_download(): void
    {
        $patient = Patient::create([
            'patient_id' => 'PID-654321',
            'name' => 'Kamal Hossain',
            'phone' => '01900112233',
            'age' => 50,
            'gender' => 'Male',
        ]);

        $prescription = Prescription::create([
            'prescription_no' => 'RX-20261109-888',
            'patient_id' => $patient->id,
            'doctor_name' => 'Dr. Mahbubur Rahman',
            'prescription_date' => now()->toDateString(),
        ]);

        $prescription->items()->create([
            'type' => 'Tab.',
            'medicine_name' => 'Monas',
            'dosage' => '0+0+1',
            'duration' => '1 month',
        ]);

        $response = $this->get(route('prescription.pdf', $prescription->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_import_medicines_command(): void
    {
        $tempCsv = 'database/data/test_medicines.csv';
        $fullPath = base_path($tempCsv);

        if (! File::exists(dirname($fullPath))) {
            File::makeDirectory(dirname($fullPath), 0755, true);
        }

        $fp = fopen($fullPath, 'w');
        fputcsv($fp, ['dosage_form', 'brand_name', 'generic_name', 'strength', 'manufacturer']);
        fputcsv($fp, ['Tab.', 'TestBrand1', 'TestGeneric1', '10mg', 'TestPharma']);
        fputcsv($fp, ['Cap.', 'TestBrand2', 'TestGeneric2', '20mg', 'TestPharma']);
        fclose($fp);

        $this->artisan('medicines:import', ['file' => $tempCsv])
            ->assertExitCode(0);

        $this->assertDatabaseHas('medicines', [
            'brand_name' => 'TestBrand1',
            'generic_name' => 'TestGeneric1',
        ]);

        $this->assertDatabaseHas('medicines', [
            'brand_name' => 'TestBrand2',
            'generic_name' => 'TestGeneric2',
        ]);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
