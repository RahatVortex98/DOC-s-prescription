<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWhatsAppWebhook;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle Meta Webhook Verification Challenge (GET /webhooks/whatsapp).
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        $expectedToken = config('services.whatsapp.verify_token');

        if ($mode === 'subscribe' && ! empty($expectedToken) && hash_equals((string) $expectedToken, (string) $token)) {
            return response($challenge, 200, ['Content-Type' => 'text/plain']);
        }

        Log::warning('Meta WhatsApp webhook verification failed', [
            'mode' => $mode,
            'provided_token' => $token,
            'ip' => $request->ip(),
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle Incoming Meta Webhook Notifications (POST /webhooks/whatsapp).
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify Meta HMAC-SHA256 Signature
        if (! $this->isValidSignature($request)) {
            Log::warning('Meta WhatsApp webhook signature verification rejected', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->all();
        if (empty($payload)) {
            return response()->json(['status' => 'empty_payload'], 200);
        }

        // 2. Extract Event Key for Idempotency
        $eventKey = $this->extractEventKey($payload, $request->getContent());

        // Check if this event was already processed or is actively processing
        $existing = WebhookEvent::where('event_key', $eventKey)->first();
        if ($existing && in_array($existing->processing_status, [WebhookEvent::STATUS_PROCESSED, WebhookEvent::STATUS_PROCESSING], true)) {
            return response()->json(['status' => 'already_processed'], 200);
        }

        // 3. Record Webhook Event in Ledger
        $webhookEvent = $existing ?? WebhookEvent::create([
            'event_key' => $eventKey,
            'event_type' => (string) ($payload['entry'][0]['changes'][0]['field'] ?? 'messages'),
            'payload' => $payload,
            'processing_status' => WebhookEvent::STATUS_PENDING,
            'attempts' => 0,
        ]);

        // 4. Dispatch Asynchronous Background Job
        ProcessWhatsAppWebhook::dispatch($webhookEvent->id);

        // 5. Acknowledge Meta Promptly (< 1500ms)
        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Validate Meta's X-Hub-Signature-256 header.
     */
    protected function isValidSignature(Request $request): bool
    {
        $appSecret = config('services.whatsapp.app_secret');

        // If no app secret is configured in environment (e.g. initial setup without secret), permit
        if (empty($appSecret)) {
            return true;
        }

        $headerSignature = (string) $request->header('X-Hub-Signature-256');
        if (empty($headerSignature) || ! str_starts_with($headerSignature, 'sha256=')) {
            return false;
        }

        $expectedHash = hash_hmac('sha256', $request->getContent(), (string) $appSecret);
        $providedHash = substr($headerSignature, 7);

        return hash_equals($expectedHash, $providedHash);
    }

    /**
     * Extract a unique deterministic key for webhook deduplication.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function extractEventKey(array $payload, string $rawContent): string
    {
        // Try extracting specific Meta Message ID (wamid)
        $wamid = $payload['entry'][0]['changes'][0]['value']['messages'][0]['id'] ?? null;
        if (! empty($wamid)) {
            return 'msg_'.$wamid;
        }

        // Try extracting status update ID
        $statusId = $payload['entry'][0]['changes'][0]['value']['statuses'][0]['id'] ?? null;
        if (! empty($statusId)) {
            $status = $payload['entry'][0]['changes'][0]['value']['statuses'][0]['status'] ?? 'unknown';

            return 'status_'.$statusId.'_'.$status;
        }

        // Fallback to entry ID with change field
        $entryId = $payload['entry'][0]['id'] ?? null;
        $timestamp = $payload['entry'][0]['time'] ?? null;
        if (! empty($entryId) && ! empty($timestamp)) {
            return 'entry_'.$entryId.'_'.$timestamp;
        }

        // Ultimate deterministic SHA-256 hash of raw content
        return 'hash_'.hash('sha256', $rawContent);
    }
}
