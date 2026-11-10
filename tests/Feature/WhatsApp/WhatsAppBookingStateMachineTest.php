<?php

namespace Tests\Feature\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Tenant;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppIntegration;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\ConversationManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppBookingStateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Chamber $chamberA;

    protected Chamber $chamberB;

    protected WhatsAppIntegration $integrationA;

    protected WhatsAppIntegration $integrationB;

    protected ConversationManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'slug' => 'clinic-alpha',
            'doctor_name' => 'Dr. Alpha Rahman',
            'bmdc_no' => 'A-11111',
            'status' => 'active',
        ]);

        $this->chamberA = Chamber::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Dhanmondi Chamber',
            'doctor_name' => 'Dr. Alpha Rahman',
            'address' => 'Road 27, Dhanmondi, Dhaka',
            'visiting_hours' => '5:00 PM - 9:00 PM',
            'is_default' => true,
        ]);

        $this->integrationA = WhatsAppIntegration::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'waba_id' => 'waba_alpha',
            'phone_number_id' => 'phone_alpha_111',
            'display_phone_number' => '+8801711111111',
            'access_token' => 'token_alpha',
            'status' => WhatsAppIntegration::STATUS_CONNECTED,
        ]);

        $this->tenantB = Tenant::create([
            'slug' => 'clinic-beta',
            'doctor_name' => 'Dr. Beta Khan',
            'bmdc_no' => 'B-22222',
            'status' => 'active',
        ]);

        $this->chamberB = Chamber::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Uttara Chamber',
            'doctor_name' => 'Dr. Beta Khan',
            'is_default' => true,
        ]);

        $this->integrationB = WhatsAppIntegration::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'waba_id' => 'waba_beta',
            'phone_number_id' => 'phone_beta_222',
            'display_phone_number' => '+8801722222222',
            'access_token' => 'token_beta',
            'status' => WhatsAppIntegration::STATUS_CONNECTED,
        ]);

        $this->manager = app(ConversationManager::class);
    }

    public function test_initial_greeting_presents_main_menu(): void
    {
        Queue::fake();

        $conv = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'wa_id' => '8801711000000',
            'current_step' => 'IDLE',
        ]);

        $msg = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.1',
            'direction' => 'inbound',
            'message_body' => 'Hello',
        ]);

        $this->manager->handleInbound($conv, $msg, $this->integrationA);

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return str_contains($job->payload['text'], 'Welcome to Dr. Alpha Rahman\'s Clinic')
                && str_contains($job->payload['text'], '1️⃣ *Book New Appointment*');
        });
    }

    public function test_end_to_end_deterministic_booking_flow(): void
    {
        Queue::fake();

        $conv = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'wa_id' => '8801711999999',
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        // Step 1: Patient starts booking by sending "1"
        $msg1 = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.B1',
            'direction' => 'inbound',
            'message_body' => '1',
        ]);
        $this->manager->handleInbound($conv, $msg1, $this->integrationA);

        $conv->refresh();
        $this->assertEquals(WhatsAppConversation::STEP_AWAITING_NAME, $conv->current_step);

        // Step 2: Patient sends Name "Mohammad Rahim"
        // (Since Tenant A has only 1 chamber, it auto-selects and moves straight to STEP_AWAITING_DATE)
        $msg2 = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.B2',
            'direction' => 'inbound',
            'message_body' => 'Mohammad Rahim',
        ]);
        $this->manager->handleInbound($conv, $msg2, $this->integrationA);

        $conv->refresh();
        $this->assertEquals('Mohammad Rahim', $conv->state_data['patient_name']);
        $this->assertEquals(WhatsAppConversation::STEP_AWAITING_DATE, $conv->current_step);

        // Step 3: Patient selects date by sending "2" (Tomorrow)
        $msg3 = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.B3',
            'direction' => 'inbound',
            'message_body' => '2',
        ]);
        $this->manager->handleInbound($conv, $msg3, $this->integrationA);

        $conv->refresh();
        $this->assertEquals(WhatsAppConversation::STEP_AWAITING_SLOT, $conv->current_step);
        $this->assertEquals(Carbon::tomorrow()->toDateString(), $conv->state_data['appointment_date']);

        // Step 4: Patient selects slot "1" (first available time slot)
        $msg4 = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.B4',
            'direction' => 'inbound',
            'message_body' => '1',
        ]);
        $this->manager->handleInbound($conv, $msg4, $this->integrationA);

        $conv->refresh();
        $this->assertEquals(WhatsAppConversation::STEP_AWAITING_CONFIRMATION, $conv->current_step);
        $this->assertNotEmpty($conv->state_data['appointment_time']);

        // Step 5: Patient confirms by sending "YES"
        $msg5 = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.B5',
            'direction' => 'inbound',
            'message_body' => 'YES',
        ]);
        $this->manager->handleInbound($conv, $msg5, $this->integrationA);

        // Assert Appointment was created in DB
        $appointment = Appointment::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantA->id)
            ->where('patient_phone', '8801711999999')
            ->first();

        $this->assertNotNull($appointment);
        $this->assertEquals('Mohammad Rahim', $appointment->patient_name);
        $this->assertEquals($this->chamberA->id, $appointment->chamber_id);
        $this->assertEquals(Appointment::SOURCE_WHATSAPP, $appointment->booking_source);
        $this->assertStringStartsWith('BK-', $appointment->booking_reference);
        $this->assertEquals(1, $appointment->serial_no);
        $this->assertEquals('confirmed', $appointment->confirmation_status);

        // Assert confirmation message was dispatched
        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) use ($appointment) {
            return str_contains($job->payload['text'], 'Appointment Confirmed')
                && str_contains($job->payload['text'], "#{$appointment->serial_no}")
                && str_contains($job->payload['text'], $appointment->booking_reference);
        });
    }

    public function test_status_command_returns_scheduled_appointment(): void
    {
        Queue::fake();

        $conv = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'wa_id' => '8801700223344',
            'current_step' => 'IDLE',
        ]);

        $app = Appointment::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'chamber_id' => $this->chamberA->id,
            'serial_no' => 5,
            'patient_name' => 'Status Patient',
            'patient_phone' => '8801700223344',
            'patient_age' => 40,
            'patient_gender' => 'Male',
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'appointment_time' => '06:00 PM',
            'booking_source' => Appointment::SOURCE_WHATSAPP,
            'booking_reference' => 'BK-TEST55',
            'status' => 'waiting',
        ]);

        $msg = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.S1',
            'direction' => 'inbound',
            'message_body' => 'STATUS',
        ]);

        $this->manager->handleInbound($conv, $msg, $this->integrationA);

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return str_contains($job->payload['text'], 'Your Upcoming Appointment Details')
                && str_contains($job->payload['text'], '#5')
                && str_contains($job->payload['text'], 'BK-TEST55');
        });
    }

    public function test_cancel_command_cancels_active_appointment(): void
    {
        Queue::fake();

        $conv = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'wa_id' => '8801700334455',
            'current_step' => 'IDLE',
        ]);

        $app = Appointment::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'chamber_id' => $this->chamberA->id,
            'serial_no' => 3,
            'patient_name' => 'Cancel Patient',
            'patient_phone' => '8801700334455',
            'patient_age' => 35,
            'patient_gender' => 'Female',
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'booking_source' => Appointment::SOURCE_WHATSAPP,
            'booking_reference' => 'BK-CANCEL99',
            'status' => 'waiting',
        ]);

        $msg = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.C1',
            'direction' => 'inbound',
            'message_body' => 'CANCEL',
        ]);

        $this->manager->handleInbound($conv, $msg, $this->integrationA);

        $app->refresh();
        $this->assertEquals('cancelled', $app->status);
        $this->assertEquals('cancelled', $app->confirmation_status);

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return str_contains($job->payload['text'], 'has been *cancelled*');
        });
    }

    public function test_medical_advice_guardrail_blocks_diagnosis_requests(): void
    {
        Queue::fake();

        $conv = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'wa_id' => '8801700445566',
            'current_step' => 'IDLE',
        ]);

        $msg = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.M1',
            'direction' => 'inbound',
            'message_body' => 'Doctor which medicine should I take for high fever and cough?',
        ]);

        $this->manager->handleInbound($conv, $msg, $this->integrationA);

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return str_contains($job->payload['text'], 'Medical Policy Notice')
                && str_contains($job->payload['text'], 'cannot provide clinical diagnoses');
        });
    }

    public function test_human_takeover_mode_silences_automated_bot_replies(): void
    {
        Queue::fake();

        $conv = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'wa_id' => '8801700556677',
            'current_step' => WhatsAppConversation::STEP_HUMAN_TAKEOVER,
            'status' => WhatsAppConversation::STATUS_WAITING_HUMAN,
        ]);

        $msg = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.H1',
            'direction' => 'inbound',
            'message_body' => 'Can someone please answer me?',
        ]);

        $this->manager->handleInbound($conv, $msg, $this->integrationA);

        // Since it is in HUMAN_TAKEOVER, the bot must NOT push any automated replies
        Queue::assertNothingPushed();
    }
}
