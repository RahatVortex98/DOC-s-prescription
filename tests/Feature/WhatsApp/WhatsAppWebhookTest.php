<?php

namespace Tests\Feature\WhatsApp;

use App\Jobs\ProcessWhatsAppWebhook;
use App\Models\Tenant;
use App\Models\WebhookEvent;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppIntegration;
use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected WhatsAppIntegration $integrationA;

    protected WhatsAppIntegration $integrationB;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.whatsapp.verify_token', 'test_secret_verify_token_123');
        Config::set('services.whatsapp.app_secret', 'test_meta_app_secret_456');

        $this->tenantA = Tenant::create([
            'slug' => 'clinic-alpha',
            'doctor_name' => 'Dr. Alpha Rahman',
            'bmdc_no' => 'A-11111',
            'status' => 'active',
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

        $this->integrationB = WhatsAppIntegration::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'waba_id' => 'waba_beta',
            'phone_number_id' => 'phone_beta_222',
            'display_phone_number' => '+8801722222222',
            'access_token' => 'token_beta',
            'status' => WhatsAppIntegration::STATUS_CONNECTED,
        ]);
    }

    public function test_meta_webhook_challenge_verification_succeeds_with_valid_token(): void
    {
        $response = $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=test_secret_verify_token_123&hub.challenge=CHALLENGE_STRING_12345');

        $response->assertStatus(200);
        $this->assertEquals('CHALLENGE_STRING_12345', $response->getContent());
    }

    public function test_meta_webhook_challenge_fails_with_invalid_token(): void
    {
        $response = $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=wrong_token&hub.challenge=CHALLENGE_STRING_12345');

        $response->assertStatus(403);
    }

    public function test_post_webhook_with_invalid_signature_is_rejected(): void
    {
        $payload = ['object' => 'whatsapp_business_account'];
        $raw = json_encode($payload);

        $response = $this->call(
            'POST',
            '/webhooks/whatsapp',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => 'sha256=invalid_signature_hash',
            ],
            $raw
        );

        $response->assertStatus(403);
    }

    public function test_post_webhook_with_valid_signature_stores_event_and_dispatches_job(): void
    {
        Queue::fake();

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'entry_123',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => '8801711111111',
                                    'phone_number_id' => 'phone_alpha_111',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.TEST_MSG_1',
                                        'from' => '8801999888777',
                                        'timestamp' => time(),
                                        'type' => 'text',
                                        'text' => ['body' => 'Hello doctor'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $raw = json_encode($payload);
        $signature = 'sha256='.hash_hmac('sha256', $raw, 'test_meta_app_secret_456');

        $response = $this->call(
            'POST',
            '/webhooks/whatsapp',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
            ],
            $raw
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'received']);

        $this->assertDatabaseHas('webhook_events', [
            'event_key' => 'msg_wamid.TEST_MSG_1',
            'processing_status' => 'pending',
        ]);

        Queue::assertPushed(ProcessWhatsAppWebhook::class);
    }

    public function test_duplicate_webhook_delivery_is_acknowledged_without_duplicate_processing(): void
    {
        Queue::fake();

        // Pre-create already processed event
        WebhookEvent::create([
            'event_key' => 'msg_wamid.ALREADY_SEEN',
            'event_type' => 'messages',
            'payload' => [],
            'processing_status' => WebhookEvent::STATUS_PROCESSED,
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'entry_123',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messages' => [
                                    ['id' => 'wamid.ALREADY_SEEN'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $raw = json_encode($payload);
        $signature = 'sha256='.hash_hmac('sha256', $raw, 'test_meta_app_secret_456');

        $response = $this->call(
            'POST',
            '/webhooks/whatsapp',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
            ],
            $raw
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'already_processed']);

        Queue::assertNotPushed(ProcessWhatsAppWebhook::class);
    }

    public function test_job_processes_inbound_message_under_correct_tenant(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'entry_123',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'phone_alpha_111',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.MSG_ROUTING_TEST',
                                        'from' => '8801700112233',
                                        'timestamp' => time(),
                                        'type' => 'text',
                                        'text' => ['body' => 'I need an appointment please'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $event = WebhookEvent::create([
            'event_key' => 'msg_wamid.MSG_ROUTING_TEST',
            'event_type' => 'messages',
            'payload' => $payload,
            'processing_status' => WebhookEvent::STATUS_PENDING,
        ]);

        $job = new ProcessWhatsAppWebhook($event->id);
        $job->handle();

        $event->refresh();
        $this->assertEquals(WebhookEvent::STATUS_PROCESSED, $event->processing_status);
        $this->assertEquals($this->tenantA->id, $event->tenant_id);

        // Verify conversation was created under Tenant A
        $convA = WhatsAppConversation::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantA->id)
            ->where('wa_id', '8801700112233')
            ->first();

        $this->assertNotNull($convA);

        // Verify message was created under Tenant A
        $msgA = WhatsAppMessage::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantA->id)
            ->where('wamid', 'wamid.MSG_ROUTING_TEST')
            ->first();

        $this->assertNotNull($msgA);
        $this->assertEquals('I need an appointment please', $msgA->message_body);
        $this->assertEquals('inbound', $msgA->direction);

        // Verify Tenant B has ZERO conversations or messages from this
        $convB = WhatsAppConversation::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantB->id)
            ->where('wa_id', '8801700112233')
            ->first();
        $this->assertNull($convB);

        $msgB = WhatsAppMessage::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantB->id)
            ->where('wamid', 'wamid.MSG_ROUTING_TEST')
            ->first();
        $this->assertNull($msgB);
    }

    public function test_job_updates_message_delivery_status(): void
    {
        $conv = WhatsAppConversation::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'integration_id' => $this->integrationA->id,
            'wa_id' => '8801755555555',
        ]);

        $msg = WhatsAppMessage::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $conv->id,
            'wamid' => 'wamid.OUTBOUND_123',
            'direction' => 'outbound',
            'delivery_status' => 'sent',
            'message_body' => 'Your serial is 01',
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'entry_123',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'phone_alpha_111',
                                ],
                                'statuses' => [
                                    [
                                        'id' => 'wamid.OUTBOUND_123',
                                        'status' => 'delivered',
                                        'timestamp' => time(),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $event = WebhookEvent::create([
            'event_key' => 'status_wamid.OUTBOUND_123_delivered',
            'event_type' => 'messages',
            'payload' => $payload,
            'processing_status' => WebhookEvent::STATUS_PENDING,
        ]);

        $job = new ProcessWhatsAppWebhook($event->id);
        $job->handle();

        $msg->refresh();
        $this->assertEquals('delivered', $msg->delivery_status);
    }
}
