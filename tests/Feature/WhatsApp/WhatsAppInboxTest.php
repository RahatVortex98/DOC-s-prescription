<?php

namespace Tests\Feature\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Livewire\WhatsApp\WhatsAppInbox;
use App\Models\Patient;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppIntegration;
use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppInboxTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected User $doctorA;

    protected User $doctorB;

    protected WhatsAppIntegration $integrationA;

    protected WhatsAppIntegration $integrationB;

    protected Patient $patientA;

    protected Patient $patientB;

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

        $this->tenantB = Tenant::create([
            'slug' => 'clinic-beta',
            'doctor_name' => 'Dr. Beta Khan',
            'bmdc_no' => 'B-22222',
            'status' => 'active',
        ]);

        $this->doctorB = User::create([
            'name' => 'Dr. Beta Khan',
            'email' => 'beta@bikrify.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'tenant_id' => $this->tenantB->id,
        ]);

        $this->integrationA = WhatsAppIntegration::create([
            'tenant_id' => $this->tenantA->id,
            'waba_id' => '100000000000001',
            'phone_number_id' => '200000000000001',
            'display_phone_number' => '+880 1711-000001',
            'verified_name' => 'Clinic Alpha Official',
            'access_token' => 'EAAG_TOKEN_A',
            'status' => WhatsAppIntegration::STATUS_CONNECTED,
            'webhook_verified' => true,
        ]);

        $this->integrationB = WhatsAppIntegration::create([
            'tenant_id' => $this->tenantB->id,
            'waba_id' => '100000000000002',
            'phone_number_id' => '200000000000002',
            'display_phone_number' => '+880 1711-000002',
            'verified_name' => 'Clinic Beta Official',
            'access_token' => 'EAAG_TOKEN_B',
            'status' => WhatsAppIntegration::STATUS_CONNECTED,
            'webhook_verified' => true,
        ]);

        $this->patientA = Patient::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'patient_id' => 'PID-A-01',
            'name' => 'Kamal Hossain',
            'phone' => '01711000001',
            'gender' => 'male',
            'age' => 45,
        ]);

        $this->patientB = Patient::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'patient_id' => 'PID-B-01',
            'name' => 'Nasreen Akter',
            'phone' => '01811000002',
            'gender' => 'female',
            'age' => 32,
        ]);
    }

    public function test_doctor_can_view_whatsapp_inbox_page(): void
    {
        $this->actingAs($this->doctorA);

        $response = $this->get(route('whatsapp.inbox'));
        $response->assertStatus(200);
        $response->assertSee('WhatsApp Patient Inbox');
        $response->assertSee('Live two-way WhatsApp patient communication');
    }

    public function test_doctor_sees_tenant_conversations_and_messages(): void
    {
        $this->actingAs($this->doctorA);

        $conversation = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'patient_id' => $this->patientA->id,
            'wa_id' => '8801711000001',
            'status' => WhatsAppConversation::STATUS_ACTIVE,
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conversation->id,
            'direction' => WhatsAppMessage::DIRECTION_INBOUND,
            'wamid' => 'wamid.HBgLMTIzNDU2Nzg5MAA=',
            'message_type' => 'text',
            'message_body' => 'Hello doctor, I need an appointment',
            'delivery_status' => WhatsAppMessage::STATUS_DELIVERED,
        ]);

        Livewire::test(WhatsAppInbox::class)
            ->assertSee('Kamal Hossain')
            ->assertSee('8801711000001')
            ->assertSee('Hello doctor, I need an appointment');
    }

    public function test_doctor_can_select_conversation(): void
    {
        $this->actingAs($this->doctorA);

        $conv1 = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'patient_id' => $this->patientA->id,
            'wa_id' => '8801711000001',
            'status' => WhatsAppConversation::STATUS_ACTIVE,
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        $conv2 = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'patient_id' => null,
            'wa_id' => '8801799999999',
            'status' => WhatsAppConversation::STATUS_ACTIVE,
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        Livewire::test(WhatsAppInbox::class)
            ->call('selectConversation', $conv2->id)
            ->assertSet('selected_conversation_id', $conv2->id)
            ->assertSee('8801799999999');
    }

    public function test_doctor_can_send_manual_reply_dispatches_job(): void
    {
        Queue::fake();
        $this->actingAs($this->doctorA);

        $conversation = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'patient_id' => $this->patientA->id,
            'wa_id' => '8801711000001',
            'status' => WhatsAppConversation::STATUS_ACTIVE,
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        Livewire::test(WhatsAppInbox::class, ['conversationId' => $conversation->id])
            ->set('reply_text', 'Yes Kamal, please visit today at 6 PM.')
            ->call('sendManualReply')
            ->assertHasNoErrors()
            ->assertSet('reply_text', '');

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) use ($conversation) {
            return $job->integrationId === $this->integrationA->id
                && $job->conversationId === $conversation->id
                && $job->toPhone === '8801711000001'
                && $job->payload['text'] === 'Yes Kamal, please visit today at 6 PM.';
        });

        // Verifying takeover mode is automatically enabled for human replies
        $conversation->refresh();
        $this->assertTrue($conversation->isHumanTakeover());
        $this->assertEquals(WhatsAppConversation::STEP_HUMAN_TAKEOVER, $conversation->current_step);
    }

    public function test_manual_reply_fails_if_integration_not_connected(): void
    {
        Queue::fake();
        $this->actingAs($this->doctorA);

        $this->integrationA->update(['status' => WhatsAppIntegration::STATUS_DISCONNECTED]);

        $conversation = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'patient_id' => $this->patientA->id,
            'wa_id' => '8801711000001',
            'status' => WhatsAppConversation::STATUS_ACTIVE,
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        Livewire::test(WhatsAppInbox::class, ['conversationId' => $conversation->id])
            ->set('reply_text', 'Hello there')
            ->call('sendManualReply')
            ->assertSee('WhatsApp Business account is not connected');

        Queue::assertNotPushed(SendWhatsAppMessage::class);
    }

    public function test_doctor_can_toggle_takeover_mode(): void
    {
        $this->actingAs($this->doctorA);

        $conversation = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'patient_id' => $this->patientA->id,
            'wa_id' => '8801711000001',
            'status' => WhatsAppConversation::STATUS_ACTIVE,
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        Livewire::test(WhatsAppInbox::class, ['conversationId' => $conversation->id])
            ->call('toggleTakeover', true);

        $conversation->refresh();
        $this->assertTrue($conversation->isHumanTakeover());
        $this->assertEquals(WhatsAppConversation::STATUS_WAITING_HUMAN, $conversation->status);

        Livewire::test(WhatsAppInbox::class, ['conversationId' => $conversation->id])
            ->call('toggleTakeover', false);

        $conversation->refresh();
        $this->assertFalse($conversation->isHumanTakeover());
        $this->assertEquals(WhatsAppConversation::STATUS_ACTIVE, $conversation->status);
    }

    public function test_doctor_can_mark_conversation_as_resolved(): void
    {
        $this->actingAs($this->doctorA);

        $conversation = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'patient_id' => $this->patientA->id,
            'wa_id' => '8801711000001',
            'status' => WhatsAppConversation::STATUS_ACTIVE,
            'current_step' => WhatsAppConversation::STEP_AWAITING_SLOT,
        ]);

        Livewire::test(WhatsAppInbox::class, ['conversationId' => $conversation->id])
            ->call('markResolved');

        $conversation->refresh();
        $this->assertEquals(WhatsAppConversation::STATUS_RESOLVED, $conversation->status);
        $this->assertEquals(WhatsAppConversation::STEP_IDLE, $conversation->current_step);
    }

    public function test_whatsapp_inbox_strictly_enforces_tenant_isolation(): void
    {
        // Conversation for Doctor B
        $convB = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'integration_id' => $this->integrationB->id,
            'patient_id' => $this->patientB->id,
            'wa_id' => '8801811000002',
            'status' => WhatsAppConversation::STATUS_ACTIVE,
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        // Doctor A logs in
        $this->actingAs($this->doctorA);

        // Scoped model query cannot find Doctor B's conversation
        $this->assertNull(WhatsAppConversation::find($convB->id));

        // Doctor A cannot see Doctor B's conversation in inbox
        Livewire::test(WhatsAppInbox::class)
            ->assertDontSee('Nasreen Akter')
            ->assertDontSee('8801811000002')
            ->call('selectConversation', $convB->id)
            ->assertSee('Conversation not found or access denied.')
            ->assertNotSet('selected_conversation_id', $convB->id);
    }
}
