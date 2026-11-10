<?php

namespace Tests\Feature\WhatsApp;

use App\Livewire\WhatsApp\WhatsAppSettings;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsAppIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected User $doctorA;

    protected User $doctorB;

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
    }

    public function test_doctor_can_view_whatsapp_settings_page(): void
    {
        $this->actingAs($this->doctorA);

        $response = $this->get(route('whatsapp.settings'));
        $response->assertStatus(200);
        $response->assertSee('WhatsApp Business Platform Integration');
        $response->assertSee('Meta Cloud API');
    }

    public function test_doctor_can_save_credentials_and_token_is_encrypted(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->actingAs($this->doctorA);

        Livewire::test(WhatsAppSettings::class)
            ->set('waba_id', 'waba_123456789')
            ->set('phone_number_id', 'phone_987654321')
            ->set('display_phone_number', '+880 1711-001122')
            ->set('access_token', 'EAAG_secret_token_1234567890_very_long')
            ->call('saveCredentials')
            ->assertSet('show_connect_modal', false)
            ->assertSet('waba_id', 'waba_123456789')
            ->assertSet('phone_number_id', 'phone_987654321');

        $integration = WhatsAppIntegration::where('tenant_id', $this->tenantA->id)->first();
        $this->assertNotNull($integration);
        $this->assertEquals(WhatsAppIntegration::STATUS_CONNECTED, $integration->status);
        $this->assertEquals('waba_123456789', $integration->waba_id);
        $this->assertEquals('+880 1711-001122', $integration->display_phone_number);

        // Model decrypts token when accessed
        $this->assertEquals('EAAG_secret_token_1234567890_very_long', $integration->access_token);

        // Raw database column is encrypted and does NOT contain raw token string
        $rawRow = DB::table('whatsapp_integrations')->where('tenant_id', $this->tenantA->id)->first();
        $this->assertStringNotContainsString('EAAG_secret_token_1234567890_very_long', (string) $rawRow->access_token);
    }

    public function test_doctor_can_disconnect_whatsapp_integration(): void
    {
        $this->actingAs($this->doctorA);

        $integration = WhatsAppIntegration::create([
            'tenant_id' => $this->tenantA->id,
            'waba_id' => 'waba_123456',
            'phone_number_id' => 'phone_123456',
            'display_phone_number' => '+8801700000000',
            'access_token' => 'dummy_token_12345678901234567890',
            'status' => WhatsAppIntegration::STATUS_CONNECTED,
        ]);

        Livewire::test(WhatsAppSettings::class)
            ->call('disconnect')
            ->assertSee('WhatsApp Business Account disconnected.');

        $integration->refresh();
        $this->assertEquals(WhatsAppIntegration::STATUS_DISCONNECTED, $integration->status);
        $this->assertNull($integration->access_token);
        $this->assertNull($integration->phone_number_id);
    }

    public function test_doctor_b_cannot_see_or_tamper_with_doctor_a_whatsapp_integration(): void
    {
        WhatsAppIntegration::create([
            'tenant_id' => $this->tenantA->id,
            'waba_id' => 'waba_alpha_private',
            'phone_number_id' => 'phone_alpha_private',
            'display_phone_number' => '+8801711111111',
            'access_token' => 'alpha_token_12345678901234567890',
            'status' => WhatsAppIntegration::STATUS_CONNECTED,
        ]);

        $this->actingAs($this->doctorB);

        Livewire::test(WhatsAppSettings::class)
            ->assertDontSee('+8801711111111')
            ->assertDontSee('waba_alpha_private')
            ->assertSee('No WhatsApp Business Number Connected');
    }

    public function test_doctor_can_send_test_ping(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '8801711001122', 'wa_id' => '8801711001122']],
                'messages' => [['id' => 'wamid.HBgLMTIzNDU2']],
            ], 200),
        ]);

        $this->actingAs($this->doctorA);

        WhatsAppIntegration::create([
            'tenant_id' => $this->tenantA->id,
            'waba_id' => 'waba_123456',
            'phone_number_id' => 'phone_123456',
            'display_phone_number' => '+8801711001122',
            'access_token' => 'token_12345678901234567890',
            'status' => WhatsAppIntegration::STATUS_CONNECTED,
        ]);

        Livewire::test(WhatsAppSettings::class)
            ->set('test_phone_number', '01711001122')
            ->call('sendTestPing')
            ->assertSee('Test message sent successfully to 01711001122!');
    }
}
