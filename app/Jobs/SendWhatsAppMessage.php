<?php

namespace App\Jobs;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppIntegration;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppClient;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $integrationId,
        public int $conversationId,
        public string $toPhone,
        public string $messageType,
        public array $payload
    ) {}

    public function handle(WhatsAppClient $client): void
    {
        $integration = WhatsAppIntegration::withoutGlobalScope('tenant')->find($this->integrationId);
        if (! $integration || ! $integration->isConnected()) {
            Log::warning("Cannot send outbound WhatsApp message: Integration [{$this->integrationId}] is inactive.");

            return;
        }

        $conversation = WhatsAppConversation::withoutGlobalScope('tenant')->find($this->conversationId);
        if (! $conversation) {
            Log::warning("Cannot send outbound WhatsApp message: Conversation [{$this->conversationId}] not found.");

            return;
        }

        try {
            $metaResponse = [];
            $bodySummary = '';

            switch ($this->messageType) {
                case 'buttons':
                    $bodySummary = (string) ($this->payload['body'] ?? '');
                    $metaResponse = $client->sendInteractiveButtons(
                        $integration,
                        $this->toPhone,
                        $bodySummary,
                        $this->payload['buttons'] ?? [],
                        $this->payload['header'] ?? null,
                        $this->payload['footer'] ?? null
                    );
                    break;

                case 'list':
                    $bodySummary = (string) ($this->payload['body'] ?? '');
                    $metaResponse = $client->sendInteractiveList(
                        $integration,
                        $this->toPhone,
                        $bodySummary,
                        $this->payload['button_label'] ?? 'View Options',
                        $this->payload['sections'] ?? [],
                        $this->payload['header'] ?? null,
                        $this->payload['footer'] ?? null
                    );
                    break;

                case 'template':
                    $bodySummary = "[Template: {$this->payload['template_name']}]";
                    $metaResponse = $client->sendTemplate(
                        $integration,
                        $this->toPhone,
                        $this->payload['template_name'],
                        $this->payload['language'] ?? 'en_US',
                        $this->payload['components'] ?? []
                    );
                    break;

                case 'text':
                default:
                    $bodySummary = (string) ($this->payload['text'] ?? '');
                    $metaResponse = $client->sendTextMessage(
                        $integration,
                        $this->toPhone,
                        $bodySummary
                    );
                    break;
            }

            $wamid = (string) ($metaResponse['messages'][0]['id'] ?? 'out_'.bin2hex(random_bytes(8)));

            // Record outbound message in ledger
            $message = new WhatsAppMessage([
                'conversation_id' => $conversation->id,
                'wamid' => $wamid,
                'direction' => WhatsAppMessage::DIRECTION_OUTBOUND,
                'message_type' => $this->messageType,
                'message_body' => $bodySummary,
                'delivery_status' => WhatsAppMessage::STATUS_SENT,
                'sent_at' => now(),
            ]);
            $message->tenant_id = $integration->tenant_id;
            $message->save();

            $conversation->update(['last_message_at' => now()]);
        } catch (Exception $e) {
            Log::error('SendWhatsAppMessage failed', [
                'integration_id' => $this->integrationId,
                'conversation_id' => $this->conversationId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
