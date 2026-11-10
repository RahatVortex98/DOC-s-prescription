<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Models\WebhookEvent;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppIntegration;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $webhookEventId) {}

    public function handle(): void
    {
        $event = WebhookEvent::find($this->webhookEventId);
        if (! $event || $event->isProcessed()) {
            return;
        }

        $event->update(['processing_status' => WebhookEvent::STATUS_PROCESSING]);

        try {
            $payload = $event->payload;
            $entries = $payload['entry'] ?? [];

            foreach ($entries as $entry) {
                $changes = $entry['changes'] ?? [];
                foreach ($changes as $change) {
                    $value = $change['value'] ?? [];
                    $phoneNumberId = (string) ($value['metadata']['phone_number_id'] ?? '');

                    if (empty($phoneNumberId)) {
                        continue;
                    }

                    // 1. Resolve WhatsApp Integration by Phone Number ID (across all tenants)
                    $integration = WhatsAppIntegration::withoutGlobalScope('tenant')
                        ->with('tenant')
                        ->where('phone_number_id', $phoneNumberId)
                        ->first();

                    if (! $integration) {
                        Log::info("No WhatsApp integration found for phone_number_id [{$phoneNumberId}]");

                        continue;
                    }

                    $tenantId = $integration->tenant_id;
                    $event->tenant_id = $tenantId;
                    $event->save();

                    // If tenant is suspended, do not process
                    if ($integration->tenant && $integration->tenant->isSuspended()) {
                        Log::warning("Skipping WhatsApp webhook for suspended tenant [{$tenantId}]");

                        continue;
                    }

                    // 2. Process Inbound Messages
                    $messages = $value['messages'] ?? [];
                    foreach ($messages as $msg) {
                        $this->processInboundMessage($integration, $msg);
                    }

                    // 3. Process Outbound Message Delivery Status Updates
                    $statuses = $value['statuses'] ?? [];
                    foreach ($statuses as $st) {
                        $this->processStatusUpdate($integration, $st);
                    }
                }
            }

            $event->markProcessed();
        } catch (Exception $e) {
            Log::error('ProcessWhatsAppWebhook job failed', [
                'event_id' => $this->webhookEventId,
                'error' => $e->getMessage(),
            ]);

            $event->markFailed($e->getMessage());

            throw $e;
        }
    }

    /**
     * Process an individual inbound WhatsApp message from a patient.
     *
     * @param  array<string, mixed>  $msg
     */
    protected function processInboundMessage(WhatsAppIntegration $integration, array $msg): void
    {
        $wamid = (string) ($msg['id'] ?? '');
        $waId = (string) ($msg['from'] ?? ''); // e.g. 8801711223344
        $type = (string) ($msg['type'] ?? 'text');
        $timestamp = isset($msg['timestamp']) ? (int) $msg['timestamp'] : null;

        if (empty($wamid) || empty($waId)) {
            return;
        }

        // Idempotency: Skip if message was already ingested
        $exists = WhatsAppMessage::withoutGlobalScope('tenant')
            ->where('wamid', $wamid)
            ->exists();

        if ($exists) {
            return;
        }

        // Extract clean text body depending on message type
        $messageBody = $this->extractMessageBody($type, $msg);

        // Find or create conversation for this patient phone under this clinic tenant
        $conversation = WhatsAppConversation::withoutGlobalScope('tenant')
            ->where('tenant_id', $integration->tenant_id)
            ->where('wa_id', $waId)
            ->first();

        if (! $conversation) {
            $conversation = new WhatsAppConversation([
                'integration_id' => $integration->id,
                'wa_id' => $waId,
                'status' => WhatsAppConversation::STATUS_ACTIVE,
                'current_step' => WhatsAppConversation::STEP_IDLE,
            ]);
            $conversation->tenant_id = $integration->tenant_id;
            $conversation->save();
        }

        // Auto-link existing patient record by phone if not linked yet
        if (! $conversation->patient_id) {
            $this->linkPatientToConversation($conversation, $waId, $integration->tenant_id);
        }

        // Record inbound message
        $receivedAt = $timestamp ? Carbon::createFromTimestamp($timestamp) : now();
        $messageRecord = new WhatsAppMessage([
            'conversation_id' => $conversation->id,
            'wamid' => $wamid,
            'direction' => WhatsAppMessage::DIRECTION_INBOUND,
            'message_type' => $type,
            'message_body' => $messageBody,
            'delivery_status' => WhatsAppMessage::STATUS_DELIVERED,
            'received_at' => $receivedAt,
            'sent_at' => $receivedAt,
        ]);
        $messageRecord->tenant_id = $integration->tenant_id;
        $messageRecord->save();

        // Update conversation touch timestamp
        $conversation->update([
            'last_inbound_at' => now(),
            'last_message_at' => now(),
        ]);

        // Hand off to conversation state machine if class exists (Phase 5)
        if (class_exists('App\\Services\\WhatsApp\\ConversationManager')) {
            try {
                app('App\\Services\\WhatsApp\\ConversationManager')->handleInbound($conversation, $messageRecord, $integration);
            } catch (Exception $e) {
                Log::error('ConversationManager handleInbound error: '.$e->getMessage());
            }
        }
    }

    /**
     * Process message delivery status callback (sent, delivered, read, failed).
     *
     * @param  array<string, mixed>  $status
     */
    protected function processStatusUpdate(WhatsAppIntegration $integration, array $status): void
    {
        $wamid = (string) ($status['id'] ?? '');
        $statusStr = (string) ($status['status'] ?? '');

        if (empty($wamid) || empty($statusStr)) {
            return;
        }

        WhatsAppMessage::withoutGlobalScope('tenant')
            ->where('tenant_id', $integration->tenant_id)
            ->where('wamid', $wamid)
            ->update([
                'delivery_status' => $statusStr,
            ]);
    }

    /**
     * Extract human-readable text from text, button, or interactive payload.
     *
     * @param  array<string, mixed>  $msg
     */
    protected function extractMessageBody(string $type, array $msg): string
    {
        return match ($type) {
            'text' => (string) ($msg['text']['body'] ?? ''),
            'interactive' => (string) (
                $msg['interactive']['button_reply']['title']
                ?? $msg['interactive']['button_reply']['id']
                ?? $msg['interactive']['list_reply']['title']
                ?? $msg['interactive']['list_reply']['id']
                ?? ''
            ),
            'button' => (string) ($msg['button']['text'] ?? $msg['button']['payload'] ?? ''),
            default => "[{$type} message]",
        };
    }

    /**
     * Attempt linking patient record by matching phone number.
     */
    protected function linkPatientToConversation(WhatsAppConversation $conversation, string $waId, int $tenantId): void
    {
        // Extract last 10 digits to match with local format e.g. 01711223344 vs 8801711223344
        $suffix = substr($waId, -10);

        $patient = Patient::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($waId, $suffix) {
                $q->where('phone', $waId)
                    ->orWhere('phone', '0'.$suffix)
                    ->orWhere('phone', 'like', '%'.$suffix);
            })
            ->first();

        if ($patient) {
            $conversation->patient_id = $patient->id;
            $conversation->save();
        }
    }
}
