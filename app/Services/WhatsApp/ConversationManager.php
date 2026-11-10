<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppIntegration;
use App\Models\WhatsAppMessage;

class ConversationManager
{
    public function __construct(protected WhatsAppBookingService $bookingService) {}

    /**
     * Process an inbound patient WhatsApp message.
     */
    public function handleInbound(
        WhatsAppConversation $conversation,
        WhatsAppMessage $message,
        WhatsAppIntegration $integration
    ): void {
        $rawText = trim((string) $message->message_body);
        $lower = strtolower($rawText);

        // 1. Human Takeover Mode Check
        if ($conversation->isHumanTakeover()) {
            if (in_array($lower, ['menu', 'restart', 'start', 'bot', 'reset'], true)) {
                $conversation->toggleHumanTakeover(false);
                $this->sendMainMenu($conversation, $integration);

                return;
            }

            // Do not send automated responses while staff is in manual conversation with the patient
            return;
        }

        // 2. Global Keywords Check (Can be triggered from any state)
        if (in_array($lower, ['agent', 'human', 'staff', 'help', 'receptionist', 'মানুষ', 'সহায়তা'], true)) {
            $conversation->toggleHumanTakeover(true);
            $msg = "🤝 *Connecting to Clinic Front Desk*\n\nOur reception staff has been notified and will reply to you directly in this chat shortly.\n\nType *MENU* anytime to return to the automated booking assistant.";
            $this->sendTextMessage($integration, $conversation, $msg);

            return;
        }

        if (in_array($lower, ['menu', 'restart', 'start', 'reset'], true)) {
            $conversation->resetToIdle();
            $this->sendMainMenu($conversation, $integration);

            return;
        }

        if (in_array($lower, ['status', 'my appointment', 'check appointment', 'সিরিয়াল', 'স্ট্যাটাস'], true)) {
            $this->bookingService->handleStatusCheck($conversation, $integration);

            return;
        }

        if (in_array($lower, ['cancel', 'বাতিল'], true)) {
            $this->bookingService->handleCancellation($conversation, $integration);

            return;
        }

        if (in_array($lower, ['reschedule', 'পুনঃনির্ধারণ'], true)) {
            $this->bookingService->handleCancellation($conversation, $integration);
            $this->bookingService->startBookingFlow($conversation, $integration);

            return;
        }

        // 3. Medical Diagnosis & Treatment Advice Guardrail
        if ($this->isMedicalQuery($lower)) {
            $doctorName = $integration->tenant?->doctor_name ?: 'the doctor';
            $msg = "⚕️ *Medical Policy Notice*\n\nThis automated assistant cannot provide clinical diagnoses, medical interpretations, or drug prescriptions.\n\nPlease schedule an in-person or chamber consultation with *{$doctorName}* for a proper medical evaluation.\n\nReply *1* or *BOOK* to schedule an appointment.";
            $this->sendTextMessage($integration, $conversation, $msg);

            return;
        }

        // 4. State Machine Routing
        switch ($conversation->current_step) {
            case WhatsAppConversation::STEP_AWAITING_NAME:
                $this->bookingService->handleName($conversation, $integration, $rawText);
                break;

            case WhatsAppConversation::STEP_AWAITING_CHAMBER:
                $this->bookingService->handleChamberSelection($conversation, $integration, $rawText);
                break;

            case WhatsAppConversation::STEP_AWAITING_DATE:
                $this->bookingService->handleDateSelection($conversation, $integration, $rawText);
                break;

            case WhatsAppConversation::STEP_AWAITING_SLOT:
                $this->bookingService->handleSlotSelection($conversation, $integration, $rawText);
                break;

            case WhatsAppConversation::STEP_AWAITING_CONFIRMATION:
                $this->bookingService->handleConfirmation($conversation, $integration, $rawText);
                break;

            case WhatsAppConversation::STEP_IDLE:
            default:
                if (in_array($lower, ['1', 'book', 'appointment', 'বুক', 'অ্যাপয়েন্টমেন্ট', 'ডাক্তার'], true)) {
                    $this->bookingService->startBookingFlow($conversation, $integration);
                } elseif (in_array($lower, ['2', 'status', 'check'], true)) {
                    $this->bookingService->handleStatusCheck($conversation, $integration);
                } elseif (in_array($lower, ['3', 'agent', 'staff'], true)) {
                    $conversation->toggleHumanTakeover(true);
                    $msg = "🤝 *Connecting to Clinic Front Desk*\n\nOur reception staff has been notified and will reply to you directly in this chat shortly.\n\nType *MENU* anytime to return to the automated booking assistant.";
                    $this->sendTextMessage($integration, $conversation, $msg);
                } else {
                    $this->sendMainMenu($conversation, $integration);
                }
                break;
        }
    }

    /**
     * Send Main Interactive Greeting Menu.
     */
    public function sendMainMenu(WhatsAppConversation $conversation, WhatsAppIntegration $integration): void
    {
        $doctorName = $integration->tenant?->doctor_name ?: 'Doctor';
        $specialty = $integration->tenant?->specialty ?: 'Specialist Practice';

        $msg = "👋 *Welcome to {$doctorName}'s Clinic*\n";
        $msg .= "_{$specialty}_\n\n";
        $msg .= "How can we assist you today? Please reply with a number:\n\n";
        $msg .= "1️⃣ *Book New Appointment*\n";
        $msg .= "2️⃣ *Check My Appointment & Serial*\n";
        $msg .= "3️⃣ *Speak with Clinic Reception Staff*\n\n";
        $msg .= 'Reply with *1*, *2*, or *3*.';

        $this->sendTextMessage($integration, $conversation, $msg);
    }

    /**
     * Detect if message asks for clinical/drug advice.
     */
    protected function isMedicalQuery(string $text): bool
    {
        $patterns = [
            '/\b(what|which)\s+(medicine|tablet|drug|antibiotic)\b/i',
            '/\b(give|prescribe)\s+(me\s+)?(medicine|prescription|drugs?)\b/i',
            '/\b(symptoms?|disease|diagnosis|fever|cough|chest pain|headache)\b/i',
            '/(কী\s*ঔষধ|ঔষধ\s*দেন|প্রেসক্রিপশন|মাথাব্যথা|বুকের\s*ব্যথা)/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper to dispatch outbound message via queue.
     */
    protected function sendTextMessage(WhatsAppIntegration $integration, WhatsAppConversation $conversation, string $text): void
    {
        SendWhatsAppMessage::dispatch(
            $integration->id,
            $conversation->id,
            $conversation->wa_id,
            'text',
            ['text' => $text]
        );
    }
}
