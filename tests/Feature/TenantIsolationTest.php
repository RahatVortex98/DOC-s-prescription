<?php

namespace Tests\Feature;

use App\Livewire\AppointmentManager;
use App\Livewire\Auth\Login;
use App\Livewire\PrescriptionBuilder;
use App\Livewire\PrescriptionList;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected User $doctorA;

    protected User $assistantA;

    protected User $doctorB;

    protected Chamber $chamberA;

    protected Chamber $chamberB;

    protected Patient $patientA;

    protected Patient $patientB;

    protected Prescription $prescriptionA;

    protected Prescription $prescriptionB;

    protected Appointment $appointmentA;

    protected Appointment $appointmentB;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('public-book:127.0.0.1');

        // Create Tenant A
        $this->tenantA = Tenant::create([
            'slug' => 'clinic-alpha',
            'doctor_name' => 'Dr. Alpha Rahman',
            'bmdc_no' => 'A-11111',
            'status' => 'active',
        ]);

        $this->doctorA = User::create([
            'name' => 'Dr. Alpha Rahman',
            'email' => 'alpha@clinic-a.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $this->tenantA->id,
            'role' => 'doctor',
        ]);

        $this->assistantA = User::create([
            'name' => 'Assistant Alpha',
            'email' => 'assistant@clinic-a.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $this->tenantA->id,
            'role' => 'assistant',
        ]);

        // Seed Tenant A scoped data (as Doctor A)
        $this->actingAs($this->doctorA);

        $this->chamberA = Chamber::create([
            'name' => 'Chamber Alpha City',
            'doctor_name' => 'Dr. Alpha Rahman',
            'specialty' => 'Nephrology',
            'is_default' => true,
        ]);

        $this->patientA = Patient::create([
            'patient_id' => 'PID-ALPHA-01',
            'name' => 'Patient Alpha Khan',
            'phone' => '01700000001',
            'age' => 45,
            'gender' => 'Male',
        ]);

        $this->prescriptionA = Prescription::create([
            'prescription_no' => 'RX-ALPHA-001',
            'patient_id' => $this->patientA->id,
            'chamber_id' => $this->chamberA->id,
            'doctor_name' => 'Dr. Alpha Rahman',
            'prescription_date' => today(),
            'diagnosis' => 'Alpha Specific Glomerulonephritis',
            'chief_complaints' => 'Alpha Severe Edema',
            'clinical_findings' => 'Alpha High BP 170/100',
        ]);

        $this->prescriptionA->items()->create([
            'type' => 'Tab.',
            'medicine_name' => 'AlphaMed Ultra',
            'dosage' => '1+0+1',
            'duration' => '14 days',
        ]);

        $this->appointmentA = Appointment::create([
            'chamber_id' => $this->chamberA->id,
            'patient_id' => $this->patientA->id,
            'serial_no' => 1,
            'patient_name' => 'Patient Alpha Khan',
            'patient_phone' => '01700000001',
            'patient_age' => 45,
            'patient_gender' => 'Male',
            'appointment_date' => today(),
            'status' => 'waiting',
            'notes' => 'Alpha consultation note',
        ]);

        // Create Tenant B
        $this->tenantB = Tenant::create([
            'slug' => 'clinic-beta',
            'doctor_name' => 'Dr. Beta Karim',
            'bmdc_no' => 'B-22222',
            'status' => 'active',
        ]);

        $this->doctorB = User::create([
            'name' => 'Dr. Beta Karim',
            'email' => 'beta@clinic-b.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $this->tenantB->id,
            'role' => 'doctor',
        ]);

        // Seed Tenant B scoped data (as Doctor B)
        $this->actingAs($this->doctorB);

        $this->chamberB = Chamber::create([
            'name' => 'Chamber Beta Central',
            'doctor_name' => 'Dr. Beta Karim',
            'specialty' => 'Cardiology',
            'is_default' => true,
        ]);

        $this->patientB = Patient::create([
            'patient_id' => 'PID-BETA-02',
            'name' => 'Patient Beta Hossain',
            'phone' => '01800000002',
            'age' => 52,
            'gender' => 'Female',
        ]);

        $this->prescriptionB = Prescription::create([
            'prescription_no' => 'RX-BETA-002',
            'patient_id' => $this->patientB->id,
            'chamber_id' => $this->chamberB->id,
            'doctor_name' => 'Dr. Beta Karim',
            'prescription_date' => today(),
            'diagnosis' => 'Beta Coronary Disease',
            'chief_complaints' => 'Beta Chest tightness',
            'clinical_findings' => 'Beta Pulse 92',
        ]);

        $this->prescriptionB->items()->create([
            'type' => 'Cap.',
            'medicine_name' => 'BetaHeart Plus',
            'dosage' => '0+0+1',
            'duration' => '30 days',
        ]);

        $this->appointmentB = Appointment::create([
            'chamber_id' => $this->chamberB->id,
            'patient_id' => $this->patientB->id,
            'serial_no' => 1,
            'patient_name' => 'Patient Beta Hossain',
            'patient_phone' => '01800000002',
            'patient_age' => 52,
            'patient_gender' => 'Female',
            'appointment_date' => today(),
            'status' => 'waiting',
            'notes' => 'Beta heart checkup',
        ]);
    }

    public function test_tenant_b_cannot_see_or_search_tenant_a_patients(): void
    {
        $this->actingAs($this->doctorB);

        // Model query isolation
        $this->assertCount(1, Patient::all());
        $this->assertEquals($this->patientB->id, Patient::first()->id);
        $this->assertNull(Patient::find($this->patientA->id));

        // Livewire search isolation in PrescriptionBuilder
        Livewire::test(PrescriptionBuilder::class)
            ->set('patient_search', '01700000001') // Phone of Patient A
            ->assertSet('patientSearchResults', [])
            ->set('patient_search', 'Alpha') // Name of Patient A
            ->assertSet('patientSearchResults', [])
            ->set('patient_search', '01800000002') // Phone of Patient B
            ->assertSet('patientSearchResults', function ($results) {
                return count($results) === 1 && $results[0]['id'] === $this->patientB->id;
            });
    }

    public function test_tenant_b_cannot_see_or_list_tenant_a_prescriptions(): void
    {
        $this->actingAs($this->doctorB);

        // Model query isolation
        $this->assertCount(1, Prescription::all());
        $this->assertEquals($this->prescriptionB->id, Prescription::first()->id);
        $this->assertNull(Prescription::find($this->prescriptionA->id));

        // Livewire prescription list isolation
        Livewire::test(PrescriptionList::class)
            ->assertSee('RX-BETA-002')
            ->assertSee('Patient Beta Hossain')
            ->assertDontSee('RX-ALPHA-001')
            ->assertDontSee('Patient Alpha Khan')
            ->set('search', 'Alpha')
            ->assertDontSee('RX-ALPHA-001');
    }

    public function test_tenant_b_cannot_see_or_list_tenant_a_appointments(): void
    {
        $this->actingAs($this->doctorB);

        // Model query isolation
        $this->assertCount(1, Appointment::all());
        $this->assertEquals($this->appointmentB->id, Appointment::first()->id);
        $this->assertNull(Appointment::find($this->appointmentA->id));

        // Livewire AppointmentManager isolation
        Livewire::test(AppointmentManager::class)
            ->assertSee('Patient Beta Hossain')
            ->assertDontSee('Patient Alpha Khan');

        // Queue in PrescriptionBuilder isolation
        Livewire::test(PrescriptionBuilder::class)
            ->assertSee('Patient Beta Hossain')
            ->assertDontSee('Patient Alpha Khan');
    }

    public function test_tenant_b_gets_404_on_tenant_a_print_pdf_and_investigation_routes(): void
    {
        $this->actingAs($this->doctorB);

        // Print view
        $this->get('/app/prescriptions/'.$this->prescriptionA->id.'/print')
            ->assertStatus(404);

        // PDF download
        $this->get('/app/prescriptions/'.$this->prescriptionA->id.'/pdf')
            ->assertStatus(404);

        // Investigation slip
        $this->get('/app/prescriptions/'.$this->prescriptionA->id.'/investigations')
            ->assertStatus(404);

        // QR standee
        $this->get('/app/chambers/'.$this->chamberA->id.'/qr-standee')
            ->assertStatus(404);
    }

    public function test_tenant_b_cannot_call_livewire_actions_with_tenant_a_ids(): void
    {
        $this->actingAs($this->doctorB);

        // 1. Cannot select Patient A -> 404
        Livewire::test(PrescriptionBuilder::class)
            ->call('selectPatient', $this->patientA->id)
            ->assertStatus(404);

        // 2. Cannot repeat Prescription A -> 404
        Livewire::test(PrescriptionBuilder::class)
            ->call('repeatPreviousPrescription', $this->prescriptionA->id)
            ->assertStatus(404);

        // 3. Cannot call Patient from Queue A -> 404
        Livewire::test(PrescriptionBuilder::class)
            ->call('callQueuePatient', $this->appointmentA->id)
            ->assertStatus(404);

        // 4. Cannot select Chamber A in PrescriptionBuilder -> 404
        Livewire::test(PrescriptionBuilder::class)
            ->call('selectChamber', $this->chamberA->id)
            ->assertStatus(404);

        // 5. Cannot open edit modal for Chamber A -> 404
        Livewire::test(PrescriptionBuilder::class)
            ->call('openEditChamberModal', $this->chamberA->id)
            ->assertStatus(404);

        // 6. Cannot select Chamber A in AppointmentManager -> 404
        Livewire::test(AppointmentManager::class)
            ->call('selectChamber', $this->chamberA->id)
            ->assertStatus(404);

        // 7. Cannot update status of Appointment A -> 404
        Livewire::test(AppointmentManager::class)
            ->call('updateStatus', $this->appointmentA->id, 'completed')
            ->assertStatus(404);

        // 8. Cannot delete Appointment A -> 404
        Livewire::test(AppointmentManager::class)
            ->call('deleteAppointment', $this->appointmentA->id)
            ->assertStatus(404);
    }

    public function test_guests_get_redirected_to_login_on_every_app_route(): void
    {
        $this->app['auth']->logout();

        $routes = [
            '/app',
            '/app/dashboard',
            '/app/prescriptions',
            '/app/appointments',
            '/app/prescriptions/'.$this->prescriptionA->id.'/print',
            '/app/prescriptions/'.$this->prescriptionA->id.'/pdf',
            '/app/prescriptions/'.$this->prescriptionA->id.'/investigations',
            '/app/chambers/'.$this->chamberA->id.'/qr-standee',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_suspended_tenant_is_blocked_on_app_routes_and_public_booking(): void
    {
        $this->tenantA->update(['status' => 'suspended']);

        // Authenticated Doctor A is blocked by middleware
        $this->actingAs($this->doctorA);
        $response = $this->get('/app');
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Login attempt by suspended doctor is rejected
        Livewire::test(Login::class)
            ->set('email', 'alpha@clinic-a.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email']);
        $this->assertGuest();

        // Public booking page for suspended tenant returns 403 Forbidden
        $this->get('/'.$this->tenantA->slug)
            ->assertStatus(403);
    }

    public function test_public_booking_works_for_guests_and_only_writes_into_slug_tenant(): void
    {
        $this->app['auth']->logout();

        $response = $this->get('/'.$this->tenantA->slug);
        $response->assertStatus(200);
        $response->assertSee('Chamber Alpha City');

        Livewire::test(AppointmentManager::class, ['tenant' => $this->tenantA])
            ->set('patient_name', 'Walk-in Patient Alpha')
            ->set('patient_age', '29')
            ->set('patient_gender', 'Male')
            ->set('patient_phone', '01988776655')
            ->set('consent', true)
            ->set('notes', 'Public booking symptoms')
            ->call('bookAppointment')
            ->assertSet('generatedSerial', 2);

        // Assert Appointment was written strictly into Tenant A
        $this->assertDatabaseHas('appointments', [
            'tenant_id' => $this->tenantA->id,
            'chamber_id' => $this->chamberA->id,
            'patient_name' => 'Walk-in Patient Alpha',
            'serial_no' => 2,
        ]);

        // Assert Tenant B did NOT receive any appointments
        $this->assertEquals(
            1,
            Appointment::withoutGlobalScope('tenant')->where('tenant_id', $this->tenantB->id)->count()
        );

        // Assert Patient was associated strictly with Tenant A
        $this->assertDatabaseHas('patients', [
            'tenant_id' => $this->tenantA->id,
            'name' => 'Walk-in Patient Alpha',
            'phone' => '01988776655',
        ]);

        $this->assertEquals(
            1,
            Patient::withoutGlobalScope('tenant')->where('tenant_id', $this->tenantB->id)->count()
        );
    }

    public function test_public_booking_requires_consent_and_rejects_past_dates(): void
    {
        $this->app['auth']->logout();

        // Attempt without consent
        Livewire::test(AppointmentManager::class, ['tenant' => $this->tenantA])
            ->set('patient_name', 'No Consent Patient')
            ->set('patient_age', '30')
            ->set('patient_gender', 'Male')
            ->set('patient_phone', '01711223344')
            ->set('consent', false)
            ->call('bookAppointment')
            ->assertHasErrors(['consent']);

        // Attempt with past date
        Livewire::test(AppointmentManager::class, ['tenant' => $this->tenantA])
            ->set('patient_name', 'Past Date Patient')
            ->set('patient_age', '30')
            ->set('patient_gender', 'Male')
            ->set('patient_phone', '01711223344')
            ->set('selected_date', today()->subDays(2)->toDateString())
            ->set('consent', true)
            ->call('bookAppointment')
            ->assertHasErrors(['selected_date']);
    }

    public function test_public_booking_rate_limiter_blocks_abuse(): void
    {
        $this->app['auth']->logout();

        RateLimiter::clear('public-book:127.0.0.1');

        $component = Livewire::test(AppointmentManager::class, ['tenant' => $this->tenantA])
            ->set('patient_name', 'Rapid Booker')
            ->set('patient_age', '25')
            ->set('patient_gender', 'Male')
            ->set('patient_phone', '01700998877')
            ->set('consent', true);

        // Exhaust 5 allowed attempts
        for ($i = 0; $i < 5; $i++) {
            $component->call('bookAppointment');
        }

        // 6th attempt must trigger rate limiter error
        $component->call('bookAppointment')
            ->assertHasErrors(['booking_limit']);
    }

    public function test_assistant_role_cannot_see_diagnosis_and_clinical_fields(): void
    {
        $this->actingAs($this->assistantA);

        // In PrescriptionBuilder: On Examination and Diagnosis sections are restricted
        $builder = Livewire::test(PrescriptionBuilder::class);
        $builder->assertDontSee('On Examination (O/E)');
        $builder->assertDontSee('Diagnosis');
        $builder->assertSee('Chief Complaints');
        $builder->assertSee('Patient Vitals');

        // In PrescriptionList: Diagnosis is masked as [Restricted]
        $list = Livewire::test(PrescriptionList::class);
        $list->assertSee('[Restricted]');
        $list->assertDontSee('Alpha Specific Glomerulonephritis');
    }
}
