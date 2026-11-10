<?php

namespace Tests\Feature;

use App\Livewire\AppointmentManager;
use App\Livewire\DoctorDashboard;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentSourceFilterTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $doctor;

    protected Chamber $chamber;

    protected AppointmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'slug' => 'test-clinic',
            'doctor_name' => 'Dr. Rafiqul Hasan',
            'bmdc_no' => 'A-55443',
            'status' => 'active',
        ]);

        $this->doctor = User::create([
            'name' => 'Dr. Rafiqul Hasan',
            'email' => 'rafiq@bikrify.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->chamber = Chamber::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main City Chamber',
            'doctor_name' => 'Dr. Rafiqul Hasan',
            'is_default' => true,
        ]);

        $this->service = app(AppointmentService::class);
    }

    public function test_appointment_manager_can_filter_by_acquisition_channel(): void
    {
        $this->actingAs($this->doctor);

        $today = Carbon::today()->toDateString();

        $aptWa = $this->service->createAppointment([
            'chamber_id' => $this->chamber->id,
            'appointment_date' => $today,
            'patient_name' => 'WhatsApp Patient',
            'patient_age' => 30,
            'patient_gender' => 'Male',
            'patient_phone' => '01711223344',
            'booking_source' => Appointment::SOURCE_WHATSAPP,
        ]);

        $aptQr = $this->service->createAppointment([
            'chamber_id' => $this->chamber->id,
            'appointment_date' => $today,
            'patient_name' => 'QR Standee Patient',
            'patient_age' => 28,
            'patient_gender' => 'Female',
            'patient_phone' => '01811223344',
            'booking_source' => Appointment::SOURCE_QR,
        ]);

        $aptRec = $this->service->createAppointment([
            'chamber_id' => $this->chamber->id,
            'appointment_date' => $today,
            'patient_name' => 'Reception Walk-in',
            'patient_age' => 45,
            'patient_gender' => 'Male',
            'patient_phone' => '01911223344',
            'booking_source' => Appointment::SOURCE_RECEPTIONIST,
        ]);

        // Default 'all' filter shows all 3
        Livewire::test(AppointmentManager::class)
            ->set('selected_date', $today)
            ->assertSet('filter_source', 'all')
            ->assertSee($aptWa->booking_reference)
            ->assertSee($aptQr->booking_reference)
            ->assertSee($aptRec->booking_reference)
            // Filter by WhatsApp only
            ->set('filter_source', Appointment::SOURCE_WHATSAPP)
            ->assertSee($aptWa->booking_reference)
            ->assertDontSee($aptQr->booking_reference)
            ->assertDontSee($aptRec->booking_reference)
            // Filter by QR only
            ->set('filter_source', Appointment::SOURCE_QR)
            ->assertSee($aptQr->booking_reference)
            ->assertDontSee($aptWa->booking_reference)
            ->assertDontSee($aptRec->booking_reference)
            // Filter by Receptionist only
            ->set('filter_source', Appointment::SOURCE_RECEPTIONIST)
            ->assertSee($aptRec->booking_reference)
            ->assertDontSee($aptWa->booking_reference)
            ->assertDontSee($aptQr->booking_reference);
    }

    public function test_doctor_dashboard_displays_acquisition_channel_analytics(): void
    {
        $this->actingAs($this->doctor);

        $today = Carbon::today()->toDateString();

        $this->service->createAppointment([
            'chamber_id' => $this->chamber->id,
            'appointment_date' => $today,
            'patient_name' => 'Patient One',
            'patient_age' => 30,
            'patient_gender' => 'Male',
            'patient_phone' => '01711223344',
            'booking_source' => Appointment::SOURCE_WHATSAPP,
        ]);

        $this->service->createAppointment([
            'chamber_id' => $this->chamber->id,
            'appointment_date' => $today,
            'patient_name' => 'Patient Two',
            'patient_age' => 28,
            'patient_gender' => 'Female',
            'patient_phone' => '01811223344',
            'booking_source' => Appointment::SOURCE_QR,
        ]);

        Livewire::test(DoctorDashboard::class)
            ->assertSee('Acquisition Channels')
            ->assertSee('WhatsApp')
            ->assertSee('QR Standee')
            ->assertSee('Receptionist');
    }
}
