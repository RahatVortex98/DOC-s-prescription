<?php

namespace App\Livewire\WhatsApp;

use App\Models\AuditLog;
use App\Models\WhatsAppIntegration;
use App\Services\WhatsApp\WhatsAppClient;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('WhatsApp Business Integration - BikrifyCare')]
class WhatsAppSettings extends Component
{
    public ?WhatsAppIntegration $integration = null;

    public string $waba_id = '';

    public string $phone_number_id = '';

    public string $display_phone_number = '';

    public string $access_token = '';

    public string $test_phone_number = '';

    public ?string $feedback_message = null;

    public ?string $error_message = null;

    public bool $show_connect_modal = false;

    public function mount(): void
    {
        $tenantId = auth()->user()?->tenant_id;
        if (! $tenantId) {
            abort(403, 'Tenant account not found.');
        }

        $this->loadIntegration();
    }

    public function loadIntegration(): void
    {
        $tenantId = auth()->user()?->tenant_id;
        $this->integration = WhatsAppIntegration::where('tenant_id', $tenantId)->first();

        if ($this->integration) {
            $this->waba_id = (string) $this->integration->waba_id;
            $this->phone_number_id = (string) $this->integration->phone_number_id;
            $this->display_phone_number = (string) $this->integration->display_phone_number;
        }
    }

    public function openConnectModal(): void
    {
        $this->feedback_message = null;
        $this->error_message = null;
        $this->show_connect_modal = true;
    }

    public function closeConnectModal(): void
    {
        $this->show_connect_modal = false;
        $this->resetValidation();
    }

    public function saveCredentials(): void
    {
        $this->validate([
            'waba_id' => 'required|string|max:100',
            'phone_number_id' => 'required|string|max:100',
            'display_phone_number' => 'required|string|max:30',
            'access_token' => 'required|string|min:20',
        ], [
            'access_token.min' => 'Please provide a valid Meta System User or Cloud API access token.',
        ]);

        $tenantId = auth()->user()?->tenant_id;

        try {
            $integration = WhatsAppIntegration::updateOrCreate(
                ['tenant_id' => $tenantId],
                [
                    'waba_id' => trim($this->waba_id),
                    'phone_number_id' => trim($this->phone_number_id),
                    'display_phone_number' => trim($this->display_phone_number),
                    'access_token' => trim($this->access_token),
                    'status' => WhatsAppIntegration::STATUS_CONNECTED,
                    'connected_at' => now(),
                ]
            );

            // Subscribe WABA to app webhooks via Meta API
            try {
                app(WhatsAppClient::class)->subscribeAppToWaba($integration->waba_id, $this->access_token);
            } catch (Exception $e) {
                Log::warning('Automatic WABA webhook subscription skipped: '.$e->getMessage());
            }

            AuditLog::record(
                'whatsapp.connected',
                $integration,
                ['description' => "Connected WhatsApp Business phone number: {$integration->display_phone_number} (WABA: {$integration->waba_id})"]
            );

            $this->access_token = '';
            $this->loadIntegration();
            $this->show_connect_modal = false;
            $this->feedback_message = '✓ WhatsApp Business Account successfully connected and encrypted.';
        } catch (Exception $e) {
            $this->error_message = 'Failed to save credentials: '.$e->getMessage();
        }
    }

    public function disconnect(): void
    {
        if (! $this->integration) {
            return;
        }

        $displayNumber = $this->integration->display_phone_number;

        $this->integration->update([
            'status' => WhatsAppIntegration::STATUS_DISCONNECTED,
            'access_token' => null,
            'phone_number_id' => null,
        ]);

        AuditLog::record(
            'whatsapp.disconnected',
            $this->integration,
            ['description' => "Disconnected WhatsApp Business number: {$displayNumber}"]
        );

        $this->loadIntegration();
        $this->feedback_message = 'WhatsApp Business Account disconnected.';
    }

    public function sendTestPing(): void
    {
        $this->validate([
            'test_phone_number' => 'required|string|min:10',
        ]);

        if (! $this->integration || ! $this->integration->isConnected()) {
            $this->error_message = 'Please connect your WhatsApp Business account first.';

            return;
        }

        try {
            $client = app(WhatsAppClient::class);
            $doctorName = auth()->user()?->name ?: 'Doctor';
            $clinicName = auth()->user()?->tenant?->doctor_name ?: 'BikrifyCare Clinic';

            $client->sendTextMessage(
                $this->integration,
                $this->test_phone_number,
                "🩺 *{$clinicName}*\n\nHello! This is an automated test ping verifying your Meta WhatsApp Cloud API integration is live and connected.\n\nTime: ".now()->format('h:i A, d M Y')
            );

            $this->feedback_message = "✓ Test message sent successfully to {$this->test_phone_number}!";
            $this->error_message = null;
        } catch (Exception $e) {
            $this->error_message = 'Failed to send test message: '.$e->getMessage();
            $this->feedback_message = null;
        }
    }

    public function render(): View
    {
        return view('livewire.whatsapp.whatsapp-settings', [
            'integration' => $this->integration,
        ])->layout('layouts.app');
    }
}
