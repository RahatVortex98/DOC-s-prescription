<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppIntegration;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppClient
{
    protected string $graphVersion;

    protected string $baseUrl;

    protected ?string $appId;

    protected ?string $appSecret;

    public function __construct()
    {
        $this->graphVersion = (string) config('services.whatsapp.graph_version', 'v21.0');
        $this->baseUrl = "https://graph.facebook.com/{$this->graphVersion}";
        $this->appId = config('services.whatsapp.app_id');
        $this->appSecret = config('services.whatsapp.app_secret');
    }

    /**
     * Exchange Embedded Signup OAuth code for an access token.
     *
     * @return array<string, mixed>
     */
    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::asForm()->get("{$this->baseUrl}/oauth/access_token", [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'code' => $code,
        ]);

        if ($response->failed()) {
            Log::error('Meta OAuth exchange failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new Exception('Failed to exchange authorization code with Meta: '.($response->json('error.message') ?? $response->body()));
        }

        return $response->json();
    }

    /**
     * Inspect and debug an access token to extract WABA ID, scopes, and expiration.
     *
     * @return array<string, mixed>
     */
    public function debugToken(string $accessToken): array
    {
        $appToken = "{$this->appId}|{$this->appSecret}";

        $response = Http::get("{$this->baseUrl}/debug_token", [
            'input_token' => $accessToken,
            'access_token' => $appToken,
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to inspect Meta access token: '.($response->json('error.message') ?? $response->body()));
        }

        return $response->json('data', []);
    }

    /**
     * Fetch registered phone numbers for a WABA.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getWabaPhoneNumbers(string $wabaId, string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get("{$this->baseUrl}/{$wabaId}/phone_numbers");

        if ($response->failed()) {
            throw new Exception('Failed to fetch WABA phone numbers: '.($response->json('error.message') ?? $response->body()));
        }

        return $response->json('data', []);
    }

    /**
     * Subscribe app to WABA Webhooks.
     */
    public function subscribeAppToWaba(string $wabaId, string $accessToken): bool
    {
        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/{$wabaId}/subscribed_apps");

        return $response->successful() && (bool) $response->json('success', false);
    }

    /**
     * Send a standard text message.
     *
     * @return array<string, mixed>
     */
    public function sendTextMessage(WhatsAppIntegration $integration, string $toPhone, string $text): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $cleanPhone,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $text,
            ],
        ];

        return $this->postMessage($integration, $payload);
    }

    /**
     * Send quick-reply interactive buttons (up to 3 buttons).
     *
     * @param  array<int, array{id: string, title: string}>  $buttons
     * @return array<string, mixed>
     */
    public function sendInteractiveButtons(
        WhatsAppIntegration $integration,
        string $toPhone,
        string $bodyText,
        array $buttons,
        ?string $header = null,
        ?string $footer = null
    ): array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);

        $formattedButtons = [];
        foreach (array_slice($buttons, 0, 3) as $btn) {
            $formattedButtons[] = [
                'type' => 'reply',
                'reply' => [
                    'id' => (string) $btn['id'],
                    'title' => mb_substr((string) $btn['title'], 0, 20),
                ],
            ];
        }

        $interactive = [
            'type' => 'button',
            'body' => ['text' => $bodyText],
            'action' => ['buttons' => $formattedButtons],
        ];

        if ($header) {
            $interactive['header'] = ['type' => 'text', 'text' => $header];
        }

        if ($footer) {
            $interactive['footer'] = ['text' => $footer];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $cleanPhone,
            'type' => 'interactive',
            'interactive' => $interactive,
        ];

        return $this->postMessage($integration, $payload);
    }

    /**
     * Send an interactive list menu (up to 10 choices).
     *
     * @param  array<int, array{title: string, rows: array<int, array{id: string, title: string, description?: string}>}>  $sections
     * @return array<string, mixed>
     */
    public function sendInteractiveList(
        WhatsAppIntegration $integration,
        string $toPhone,
        string $bodyText,
        string $buttonLabel,
        array $sections,
        ?string $header = null,
        ?string $footer = null
    ): array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);

        $interactive = [
            'type' => 'list',
            'body' => ['text' => $bodyText],
            'action' => [
                'button' => mb_substr($buttonLabel, 0, 20),
                'sections' => $sections,
            ],
        ];

        if ($header) {
            $interactive['header'] = ['type' => 'text', 'text' => $header];
        }

        if ($footer) {
            $interactive['footer'] = ['text' => $footer];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $cleanPhone,
            'type' => 'interactive',
            'interactive' => $interactive,
        ];

        return $this->postMessage($integration, $payload);
    }

    /**
     * Send an approved Meta WhatsApp template.
     *
     * @param  array<int, array<string, mixed>>  $components
     * @return array<string, mixed>
     */
    public function sendTemplate(
        WhatsAppIntegration $integration,
        string $toPhone,
        string $templateName,
        string $languageCode = 'en_US',
        array $components = []
    ): array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $cleanPhone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => $components,
            ],
        ];

        return $this->postMessage($integration, $payload);
    }

    /**
     * Execute HTTP POST to Meta Messages Endpoint with decrypted integration token.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function postMessage(WhatsAppIntegration $integration, array $payload): array
    {
        $phoneNumberId = $integration->phone_number_id;
        if (empty($phoneNumberId)) {
            throw new Exception('WhatsApp integration is missing a valid phone_number_id.');
        }

        $accessToken = $integration->access_token;
        if (empty($accessToken)) {
            throw new Exception('WhatsApp integration is missing an access_token.');
        }

        $url = "{$this->baseUrl}/{$phoneNumberId}/messages";

        $response = Http::withToken($accessToken)
            ->timeout(10)
            ->post($url, $payload);

        if ($response->failed()) {
            Log::error('Meta WhatsApp send failure', [
                'tenant_id' => $integration->tenant_id,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            throw new Exception('Meta Cloud API error: '.($response->json('error.message') ?? $response->body()));
        }

        return $response->json();
    }
}
