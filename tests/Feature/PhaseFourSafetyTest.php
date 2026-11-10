<?php

namespace Tests\Feature;

use App\Livewire\AppointmentManager;
use App\Livewire\AuditLogViewer;
use App\Livewire\PrescriptionBuilder;
use App\Livewire\PrescriptionList;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseFourSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $doctor;

    protected Chamber $chamber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'slug' => 'dr-karim',
            'doctor_name' => 'Dr. Rezaul Karim',
            'bmdc_no' => 'A-89912',
            'status' => 'active',
        ]);

        $this->doctor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'doctor',
            'email' => 'rezaul@bikrify.com',
        ]);

        $this->chamber = Chamber::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Karim Specialized Clinic',
            'doctor_qualification' => 'MBBS, FCPS',
            'doctor_designation' => 'Associate Professor',
            'specialty' => 'Medicine',
            'phone' => '01711000000',
        ]);
    }

    public function test_patient_matching_creates_distinct_records_for_shared_phone_with_different_names(): void
    {
        $this->actingAs($this->doctor);

        // 1st patient: Father
        Livewire::test(PrescriptionBuilder::class)
            ->set('patient_name', 'Rafiqul Islam')
            ->set('patient_age', '58')
            ->set('patient_gender', 'Male')
            ->set('patient_phone', '01712345678')
            ->set('patient_allergies', 'Penicillin')
            ->set('diagnosis', 'Hypertension')
            ->set('items', [[
                'type' => 'Tab.',
                'name' => 'Amlodipine',
                'generic' => 'Amlodipine',
                'strength' => '5mg',
                'dosage' => '1+0+0',
                'duration' => '1 month',
                'instruction' => 'In the morning',
            ]])
            ->call('saveAndPrint');

        $this->assertDatabaseHas('patients', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Rafiqul Islam',
            'phone' => '01712345678',
            'allergies' => 'Penicillin',
        ]);

        // 2nd patient: Son sharing the exact same phone number
        Livewire::test(PrescriptionBuilder::class)
            ->set('patient_name', 'Tahmid Islam')
            ->set('patient_age', '19')
            ->set('patient_gender', 'Male')
            ->set('patient_phone', '01712345678')
            ->set('patient_allergies', 'Sulfa')
            ->set('diagnosis', 'Acute Bronchitis')
            ->set('items', [[
                'type' => 'Cap.',
                'name' => 'Azithromycin',
                'generic' => 'Azithromycin',
                'strength' => '500mg',
                'dosage' => '1+0+0',
                'duration' => '5 days',
                'instruction' => '1 hour before meal',
            ]])
            ->call('saveAndPrint');

        // Verify two separate patients exist with same phone
        $this->assertEquals(
            2,
            Patient::where('phone', '01712345678')->count()
        );

        $father = Patient::where('name', 'Rafiqul Islam')->first();
        $son = Patient::where('name', 'Tahmid Islam')->first();

        $this->assertNotNull($father);
        $this->assertNotNull($son);
        $this->assertNotEquals($father->id, $son->id);
        $this->assertEquals('Penicillin', $father->allergies);
        $this->assertEquals('Sulfa', $son->allergies);
    }

    public function test_public_booking_creates_distinct_patient_for_shared_phone(): void
    {
        // 1st booking
        Livewire::test(AppointmentManager::class, ['tenant' => $this->tenant])
            ->call('selectChamber', $this->chamber->id)
            ->set('selected_date', now()->addDay()->toDateString())
            ->set('patient_name', 'Momena Begum')
            ->set('patient_age', '45')
            ->set('patient_gender', 'Female')
            ->set('patient_phone', '01899999999')
            ->set('consent', true)
            ->call('bookAppointment');

        // 2nd booking with same phone, different family member
        Livewire::test(AppointmentManager::class, ['tenant' => $this->tenant])
            ->call('selectChamber', $this->chamber->id)
            ->set('selected_date', now()->addDay()->toDateString())
            ->set('patient_name', 'Nusrat Jahan')
            ->set('patient_age', '16')
            ->set('patient_gender', 'Female')
            ->set('patient_phone', '01899999999')
            ->set('consent', true)
            ->call('bookAppointment');

        $this->assertEquals(
            2,
            Patient::withoutGlobalScope('tenant')->where('phone', '01899999999')->count()
        );
    }

    public function test_allergies_stored_and_rendered_prominently_on_print(): void
    {
        $this->actingAs($this->doctor);

        $patient = Patient::create([
            'tenant_id' => $this->tenant->id,
            'patient_id' => 'PID-ALLERGY-1',
            'name' => 'Allergic Patient',
            'age' => 30,
            'gender' => 'Female',
            'phone' => '01555555555',
            'allergies' => 'Cephalosporins, Aspirin',
        ]);

        $prescription = Prescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_no' => 'RX-260101-0001',
            'patient_id' => $patient->id,
            'chamber_id' => $this->chamber->id,
            'prescription_date' => now()->toDateString(),
            'doctor_name' => $this->tenant->doctor_name,
        ]);

        $response = $this->get(route('prescription.print', $prescription->id));
        $response->assertOk();
        $response->assertSee('Drug Allergies');
        $response->assertSee('Cephalosporins, Aspirin');
    }

    public function test_soft_deletes_preserve_prescription_and_appointment_records(): void
    {
        $this->actingAs($this->doctor);

        $patient = Patient::create([
            'tenant_id' => $this->tenant->id,
            'patient_id' => 'PID-DEL-1',
            'name' => 'Delete Test Patient',
            'age' => 40,
            'gender' => 'Male',
        ]);

        $prescription = Prescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_no' => 'RX-DEL-0001',
            'patient_id' => $patient->id,
            'chamber_id' => $this->chamber->id,
            'prescription_date' => now()->toDateString(),
            'doctor_name' => $this->tenant->doctor_name,
        ]);

        $appointment = Appointment::create([
            'tenant_id' => $this->tenant->id,
            'chamber_id' => $this->chamber->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'patient_age' => $patient->age,
            'patient_gender' => $patient->gender,
            'appointment_date' => now()->toDateString(),
            'serial_no' => 1,
            'status' => 'pending',
        ]);

        // Soft delete both
        $prescription->delete();
        $appointment->delete();

        // Standard queries should not find them
        $this->assertNull(Prescription::find($prescription->id));
        $this->assertNull(Appointment::find($appointment->id));

        // WithTrashed should find them
        $this->assertNotNull(Prescription::withTrashed()->find($prescription->id));
        $this->assertNotNull(Appointment::withTrashed()->find($appointment->id));
        $this->assertNotNull(Prescription::withTrashed()->find($prescription->id)->deleted_at);
        $this->assertNotNull(Appointment::withTrashed()->find($appointment->id)->deleted_at);
    }

    public function test_prescription_voiding_marks_void_and_watermarks_print(): void
    {
        $this->actingAs($this->doctor);

        $patient = Patient::create([
            'tenant_id' => $this->tenant->id,
            'patient_id' => 'PID-VOID-1',
            'name' => 'Void Test Patient',
            'age' => 45,
            'gender' => 'Male',
        ]);

        $prescription = Prescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_no' => 'RX-VOID-0001',
            'patient_id' => $patient->id,
            'chamber_id' => $this->chamber->id,
            'prescription_date' => now()->toDateString(),
            'doctor_name' => $this->tenant->doctor_name,
            'status' => 'active',
        ]);

        $prescription->items()->create([
            'medicine_name' => 'Napa Extra 500mg',
            'type' => 'Tab.',
            'dosage' => '1+0+1',
            'duration' => '5 days',
        ]);

        // Void via PrescriptionList component
        Livewire::test(PrescriptionList::class)
            ->call('voidPrescription', $prescription->id, 'Mistake in diagnosis');

        $prescription->refresh();
        $this->assertTrue($prescription->isVoided());
        $this->assertEquals('void', $prescription->status);
        $this->assertEquals('Mistake in diagnosis', $prescription->void_reason);
        $this->assertNotNull($prescription->voided_at);

        // Check print page shows VOID watermark
        $response = $this->get(route('prescription.print', $prescription->id));
        $response->assertOk();
        $response->assertSee('VOID');
        $response->assertSee('Mistake in diagnosis');

        // Cannot repeat/clone in PrescriptionBuilder
        $builder = Livewire::test(PrescriptionBuilder::class, ['prescriptionId' => $prescription->id]);
        $this->assertNotEquals('Napa Extra 500mg', $builder->get('items.0.name'));
    }

    public function test_audit_logs_record_actions_and_viewer_shows_them(): void
    {
        $this->actingAs($this->doctor);

        $patient = Patient::create([
            'tenant_id' => $this->tenant->id,
            'patient_id' => 'PID-AUD-1',
            'name' => 'Audit Test Patient',
            'age' => 35,
            'gender' => 'Female',
        ]);

        $prescription = Prescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_no' => 'RX-AUD-0001',
            'patient_id' => $patient->id,
            'chamber_id' => $this->chamber->id,
            'prescription_date' => now()->toDateString(),
            'doctor_name' => $this->tenant->doctor_name,
        ]);

        // Print triggers audit log
        $this->get(route('prescription.print', $prescription->id));

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->doctor->id,
            'action' => 'prescription.printed',
            'subject_id' => $prescription->id,
        ]);

        // Audit viewer displays this action
        Livewire::test(AuditLogViewer::class)
            ->assertSee('prescription.printed')
            ->assertSee('RX-AUD-0001');
    }

    public function test_expiring_signed_whatsapp_share_link(): void
    {
        $patient = Patient::create([
            'tenant_id' => $this->tenant->id,
            'patient_id' => 'PID-SHARE-1',
            'name' => 'Share Test Patient',
            'age' => 28,
            'gender' => 'Male',
        ]);

        $prescription = Prescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_no' => 'RX-SHARE-0001',
            'patient_id' => $patient->id,
            'chamber_id' => $this->chamber->id,
            'prescription_date' => now()->toDateString(),
            'doctor_name' => $this->tenant->doctor_name,
        ]);

        $this->assertNotNull($prescription->public_id);

        // Generate 7-day valid signed URL
        $validSignedUrl = URL::temporarySignedRoute(
            'prescription.public_share',
            now()->addDays(7),
            ['public_id' => $prescription->public_id]
        );

        // Guest accessing with valid signed URL succeeds without login
        $response = $this->get($validSignedUrl);
        $response->assertOk();
        $response->assertSee($prescription->prescription_no);
        $response->assertSee('Share Test Patient');

        // Tampered signed URL fails with 403
        $tamperedUrl = $validSignedUrl.'tampered';
        $tamperedResponse = $this->get($tamperedUrl);
        $tamperedResponse->assertForbidden();

        // Expired URL fails with 403
        $expiredUrl = URL::temporarySignedRoute(
            'prescription.public_share',
            now()->subMinute(),
            ['public_id' => $prescription->public_id]
        );
        $expiredResponse = $this->get($expiredUrl);
        $expiredResponse->assertForbidden();
    }

    public function test_idempotent_medicine_import_command(): void
    {
        // Insert initial test record
        Medicine::create([
            'brand_name' => 'Napa Test',
            'generic_name' => 'Paracetamol',
            'dosage_form' => 'Tablet',
            'strength' => '500 mg',
            'manufacturer' => 'Beximco',
        ]);

        $initialCount = Medicine::count();

        // Create a temporary CSV with existing and new medicine
        $csvPath = database_path('data/temp_test_medicines.csv');
        $csvContent = "dosage_form,brand_name,generic_name,strength,manufacturer\n";
        $csvContent .= "Tablet,Napa Test,Paracetamol,500 mg,Beximco Pharmaceuticals Ltd.\n"; // update
        $csvContent .= "Capsule,Seclo Test,Omeprazole,20 mg,Square Pharmaceuticals Ltd.\n"; // insert

        file_put_contents($csvPath, $csvContent);

        try {
            // Run import command twice
            $this->artisan('medicines:import', ['file' => 'database/data/temp_test_medicines.csv'])
                ->assertSuccessful();

            $this->artisan('app:import-medicines', ['file' => 'database/data/temp_test_medicines.csv'])
                ->assertSuccessful();

            // Total count should increase by exactly 1 (Seclo Test), not duplicate Napa Test
            $this->assertEquals($initialCount + 1, Medicine::count());

            // Napa Test manufacturer should be updated
            $napa = Medicine::where('brand_name', 'Napa Test')->first();
            $this->assertNotNull($napa);
            $this->assertEquals('Beximco Pharmaceuticals Ltd.', $napa->manufacturer);
        } finally {
            if (file_exists($csvPath)) {
                unlink($csvPath);
            }
        }
    }
}
