<?php

namespace Tests\Feature;

use App\Livewire\AppointmentManager;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected User $doctorA;

    protected Chamber $chamberA;

    protected Chamber $chamberB;

    protected AppointmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'slug' => 'clinic-alpha',
            'doctor_name' => 'Dr. Alpha Rahman',
            'bmdc_no' => 'A-11111',
            'status' => 'active',
        ]);

        $this->doctorA = User::create([
            'name' => 'Dr. Alpha Rahman',
            'email' => 'alpha@bikrify.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'tenant_id' => $this->tenantA->id,
        ]);

        $this->chamberA = Chamber::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Chamber Alpha',
            'doctor_name' => 'Dr. Alpha Rahman',
            'is_default' => true,
        ]);

        $this->tenantB = Tenant::create([
            'slug' => 'clinic-beta',
            'doctor_name' => 'Dr. Beta Khan',
            'bmdc_no' => 'B-22222',
            'status' => 'active',
        ]);

        $this->chamberB = Chamber::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Chamber Beta',
            'doctor_name' => 'Dr. Beta Khan',
            'is_default' => true,
        ]);

        $this->service = app(AppointmentService::class);
    }

    public function test_receptionist_booking_creates_appointment_with_correct_source_and_reference(): void
    {
        $this->actingAs($this->doctorA);

        $appointment = $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'patient_name' => 'Tariqul Islam',
            'patient_age' => 35,
            'patient_gender' => 'Male',
            'patient_phone' => '01711000111',
            'booking_source' => Appointment::SOURCE_RECEPTIONIST,
        ]);

        $this->assertNotNull($appointment->id);
        $this->assertEquals($this->tenantA->id, $appointment->tenant_id);
        $this->assertEquals(Appointment::SOURCE_RECEPTIONIST, $appointment->booking_source);
        $this->assertStringStartsWith('BK-', $appointment->booking_reference);
        $this->assertEquals(1, $appointment->serial_no);
        $this->assertEquals('confirmed', $appointment->confirmation_status);
    }

    public function test_qr_and_whatsapp_bookings_store_sources_correctly_without_session_auth(): void
    {
        // QR booking (guest)
        $qrApp = $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'patient_name' => 'QR Patient',
            'patient_age' => 28,
            'patient_gender' => 'Female',
            'patient_phone' => '01811222333',
            'booking_source' => Appointment::SOURCE_QR,
        ], $this->tenantA->id);

        $this->assertEquals(Appointment::SOURCE_QR, $qrApp->booking_source);
        $this->assertEquals(1, $qrApp->serial_no);

        // WhatsApp booking (guest)
        $waApp = $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'patient_name' => 'WhatsApp Patient',
            'patient_age' => 42,
            'patient_gender' => 'Male',
            'patient_phone' => '01911444555',
            'booking_source' => Appointment::SOURCE_WHATSAPP,
        ], $this->tenantA->id);

        $this->assertEquals(Appointment::SOURCE_WHATSAPP, $waApp->booking_source);
        $this->assertEquals(2, $waApp->serial_no);
    }

    public function test_serial_numbers_increment_atomically_per_chamber_and_date(): void
    {
        $date = Carbon::tomorrow()->toDateString();

        $app1 = $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => $date,
            'patient_name' => 'First Patient',
            'patient_age' => 30,
            'patient_gender' => 'Male',
        ], $this->tenantA->id);

        $app2 = $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => $date,
            'patient_name' => 'Second Patient',
            'patient_age' => 31,
            'patient_gender' => 'Female',
        ], $this->tenantA->id);

        $this->assertEquals(1, $app1->serial_no);
        $this->assertEquals(2, $app2->serial_no);

        // Different date should reset to 1
        $appNextDay = $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'patient_name' => 'Third Patient',
            'patient_age' => 32,
            'patient_gender' => 'Male',
        ], $this->tenantA->id);

        $this->assertEquals(1, $appNextDay->serial_no);
    }

    public function test_cannot_book_with_chamber_belonging_to_another_tenant(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("does not belong to clinic tenant [{$this->tenantA->id}]");

        // Attempt to book Chamber B under Tenant A
        $this->service->createAppointment([
            'chamber_id' => $this->chamberB->id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'patient_name' => 'Hacker Patient',
            'patient_age' => 25,
            'patient_gender' => 'Male',
        ], $this->tenantA->id);
    }

    public function test_cannot_book_appointment_in_the_past(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Appointment date cannot be in the past.');

        $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => Carbon::yesterday()->toDateString(),
            'patient_name' => 'Past Patient',
            'patient_age' => 25,
            'patient_gender' => 'Male',
        ], $this->tenantA->id);
    }

    public function test_patient_is_reused_when_phone_and_name_match_under_same_tenant(): void
    {
        $date = Carbon::tomorrow()->toDateString();

        $app1 = $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => $date,
            'patient_name' => 'Rahim Uddin',
            'patient_age' => 50,
            'patient_gender' => 'Male',
            'patient_phone' => '01700999888',
        ], $this->tenantA->id);

        $app2 = $this->service->createAppointment([
            'chamber_id' => $this->chamberA->id,
            'appointment_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'patient_name' => 'Rahim Uddin',
            'patient_age' => 50,
            'patient_gender' => 'Male',
            'patient_phone' => '01700999888',
        ], $this->tenantA->id);

        $this->assertNotNull($app1->patient_id);
        $this->assertEquals($app1->patient_id, $app2->patient_id);
        $this->assertEquals(1, Patient::withoutGlobalScope('tenant')->where('tenant_id', $this->tenantA->id)->where('phone', '01700999888')->count());
    }

    public function test_livewire_public_booking_tags_qr_source_and_generates_reference(): void
    {
        Livewire::test(AppointmentManager::class, ['tenant' => $this->tenantA])
            ->call('selectChamber', $this->chamberA->id)
            ->set('patient_name', 'QR Online Patient')
            ->set('patient_age', '29')
            ->set('patient_gender', 'Female')
            ->set('patient_phone', '01755123456')
            ->set('consent', true)
            ->call('bookAppointment')
            ->assertSet('generatedSerial', 1);

        $appointment = Appointment::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantA->id)
            ->where('patient_phone', '01755123456')
            ->first();

        $this->assertNotNull($appointment);
        $this->assertEquals(Appointment::SOURCE_QR, $appointment->booking_source);
        $this->assertStringStartsWith('BK-', $appointment->booking_reference);
    }
}
