<?php

namespace Tests\Feature;

use App\Livewire\AppointmentManager;
use App\Livewire\PrescriptionBuilder;
use App\Livewire\PrescriptionList;
use App\Models\Appointment;
use App\Models\Chamber;
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
        Chamber::create([
            'name' => 'Popular Diagnostic Center',
            'doctor_name' => 'Dr. Mahbubur Rahman',
            'specialty' => 'Nephrology / CKD Specialist',
            'is_default' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Bikrify Prescription Studio');
        $response->assertSee('Popular Diagnostic Center');
    }

    public function test_appointment_booking_and_serial_number_generation(): void
    {
        $chamber = Chamber::create([
            'name' => 'Banani Chamber',
            'doctor_name' => 'Dr. Mahbubur Rahman',
            'specialty' => 'Nephrology / CKD Specialist',
            'is_default' => true,
        ]);

        Livewire::test(AppointmentManager::class)
            ->set('selected_chamber_id', $chamber->id)
            ->set('patient_name', 'Mizanur Rahman')
            ->set('patient_age', '48')
            ->set('patient_gender', 'Male')
            ->set('patient_phone', '01711223399')
            ->set('appointment_time', '05:00 PM')
            ->set('notes', 'Swelling of legs and high BP')
            ->call('bookAppointment')
            ->assertSet('generatedSerial', 1);

        $this->assertDatabaseHas('appointments', [
            'chamber_id' => $chamber->id,
            'patient_name' => 'Mizanur Rahman',
            'serial_no' => 1,
            'status' => 'waiting',
        ]);
    }

    public function test_chamber_qr_standee_poster_loads_successfully(): void
    {
        $chamber = Chamber::create([
            'name' => 'Square Hospital',
            'doctor_name' => 'Dr. Mahbubur Rahman',
            'specialty' => 'Nephrology / CKD Specialist',
            'is_default' => true,
        ]);

        $qrResponse = $this->get('/chambers/'.$chamber->id.'/qr-standee');
        $qrResponse->assertStatus(200);
        $qrResponse->assertSee('Square Hospital');
        $qrResponse->assertSee('Scan with Phone Camera');
    }

    public function test_doctor_can_call_patient_from_today_queue_and_auto_fill(): void
    {
        $chamber = Chamber::create([
            'name' => 'Square Hospital',
            'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
            'specialty' => 'Nephrology / CKD Specialist',
            'is_default' => true,
        ]);

        $app = Appointment::create([
            'chamber_id' => $chamber->id,
            'serial_no' => 1,
            'patient_name' => 'Tariqul Islam',
            'patient_age' => 54,
            'patient_gender' => 'Male',
            'patient_phone' => '01811556677',
            'appointment_date' => today(),
            'status' => 'waiting',
            'notes' => 'Chronic kidney disease checkup',
        ]);

        Livewire::test(PrescriptionBuilder::class)
            ->call('callQueuePatient', $app->id)
            ->assertSet('patient_name', 'Tariqul Islam')
            ->assertSet('patient_age', '54')
            ->assertSet('patient_gender', 'Male')
            ->assertSet('patient_phone', '01811556677')
            ->assertSet('chief_complaints', 'Chronic kidney disease checkup');

        $this->assertDatabaseHas('appointments', [
            'id' => $app->id,
            'status' => 'with_doctor',
        ]);
    }

    public function test_one_click_specialty_bundle_and_drug_kit_application(): void
    {
        Livewire::test(PrescriptionBuilder::class)
            ->set('specialty', 'Nephrology / CKD Specialist')
            ->call('applySpecialtyBundle')
            ->assertSet('diagnosis', 'Chronic Kidney Disease (Stage 4), Hypertensive Nephrosclerosis, Renal Anemia')
            ->call('applyDrugKit', 0) // Apply CKD Core Drug Kit
            ->assertSet('items.0.name', 'Ketosteril')
            ->assertSet('items.1.name', 'Sodibicarb')
            ->assertSet('items.2.name', 'Febux')
            ->assertSet('items.3.name', 'Calcichew D');
    }

    public function test_vitals_calculator_inserts_clean_oe(): void
    {
        Livewire::test(PrescriptionBuilder::class)
            ->set('vital_bp_systolic', '160')
            ->set('vital_bp_diastolic', '95')
            ->set('vital_pulse', '82')
            ->set('vital_weight', '70')
            ->set('vital_height_cm', '165')
            ->call('insertVitals')
            ->assertSet('clinical_findings', function ($findings) {
                return str_contains($findings, 'BP: 160/95 mmHg (Stage 2 HTN)')
                    && str_contains($findings, 'Pulse: 82/min')
                    && str_contains($findings, 'BMI: 25.7');
            });
    }

    public function test_medicine_search_returns_multiple_variants_including_napa_extra(): void
    {
        Medicine::create([
            'brand_name' => 'Napa',
            'generic_name' => 'Paracetamol',
            'dosage_form' => 'Tab.',
            'strength' => '500mg',
            'manufacturer' => 'Beximco',
        ]);

        Medicine::create([
            'brand_name' => 'Napa Extra',
            'generic_name' => 'Paracetamol + Caffeine',
            'dosage_form' => 'Tab.',
            'strength' => '500mg+65mg',
            'manufacturer' => 'Beximco',
        ]);

        Livewire::test(PrescriptionBuilder::class)
            ->set('search', 'napa')
            ->assertSet('searchResults', function ($results) {
                $names = array_column($results, 'brand_name');

                return in_array('Napa', $names)
                    && in_array('Napa Extra', $names);
            });
    }

    public function test_old_patient_lookup_and_repeat_previous_prescription(): void
    {
        $patient = Patient::create([
            'patient_id' => 'PID-OLD101',
            'name' => 'Karim Ullah',
            'phone' => '01712345678',
            'age' => 58,
            'gender' => 'Male',
        ]);

        $pastRx = Prescription::create([
            'prescription_no' => 'RX-20261101-111',
            'patient_id' => $patient->id,
            'doctor_name' => 'Dr. Mahbubur Rahman',
            'prescription_date' => '2026-11-01',
            'diagnosis' => 'CKD Stage 4',
        ]);

        $pastRx->items()->create([
            'type' => 'Tab.',
            'medicine_name' => 'Ketosteril',
            'dosage' => '1+1+1',
            'duration' => '1 month',
            'instructions' => 'With meal',
        ]);

        Livewire::test(PrescriptionBuilder::class)
            ->set('patient_search', '01712345678')
            ->assertSet('patientSearchResults', function ($results) {
                return count($results) === 1 && $results[0]['name'] === 'Karim Ullah';
            })
            ->call('selectPatient', $patient->id)
            ->assertSet('is_existing_patient', true)
            ->assertSet('patient_name', 'Karim Ullah')
            ->assertSet('patient_age', '58')
            ->call('repeatPreviousPrescription', $pastRx->id)
            ->assertSet('items.0.name', 'Ketosteril')
            ->assertSet('items.0.dosage', '1+1+1')
            ->assertSet('diagnosis', 'CKD Stage 4');
    }

    public function test_prescriptions_list_page_loads_and_filters(): void
    {
        $patient = Patient::create([
            'patient_id' => 'PID-112233',
            'name' => 'Anowara Begum',
            'age' => 60,
            'gender' => 'Female',
        ]);

        Prescription::create([
            'prescription_no' => 'RX-20261109-777',
            'patient_id' => $patient->id,
            'doctor_name' => 'Dr. Mahbubur Rahman',
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Glomerulonephritis',
        ]);

        Livewire::test(PrescriptionList::class)
            ->set('search', 'Anowara')
            ->assertSee('RX-20261109-777')
            ->assertSee('Anowara Begum');
    }

    public function test_save_and_print_creates_patient_and_prescription_and_redirects(): void
    {
        $chamber = Chamber::create([
            'name' => 'Banani Chamber',
            'doctor_name' => 'Dr. Mahbubur Rahman',
            'specialty' => 'Nephrology / CKD Specialist',
            'is_default' => true,
        ]);

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
            ->set('items.0.name', 'Napa Extra')
            ->set('items.0.type', 'Tab.')
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

        $this->assertDatabaseHas('prescriptions', [
            'diagnosis' => 'Viral Fever',
            'chamber_name' => 'Banani Chamber',
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
            'doctor_name' => 'Prof. Dr. Mahbubur Rahman',
            'doctor_qualification' => 'MBBS, FCPS, MD (Nephrology)',
            'doctor_designation' => 'Professor & Senior Consultant',
            'chamber_name' => 'Popular Diagnostic Center - Dhanmondi',
            'chamber_address' => 'House 16, Road 2, Dhanmondi',
            'chamber_phone' => '+880 1819-556677',
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
        $response->assertSee('Popular Diagnostic Center - Dhanmondi');
        $response->assertSee('Seclo');
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
