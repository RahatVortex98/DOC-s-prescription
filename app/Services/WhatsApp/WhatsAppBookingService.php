<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppIntegration;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class WhatsAppBookingService
{
    public function __construct(protected AppointmentService $appointmentService) {}

    /**
     * Start booking flow: Check if patient name is known, otherwise prompt for full name.
     */
    public function startBookingFlow(WhatsAppConversation $conversation, WhatsAppIntegration $integration): void
    {
        $tenantId = $integration->tenant_id;
        $doctorName = $integration->tenant?->doctor_name ?: 'Doctor';

        // Check if patient name already exists from linked patient or state data
        $patient = $conversation->patient_id
            ? Patient::withoutGlobalScope('tenant')->where('tenant_id', $tenantId)->find($conversation->patient_id)
            : null;

        $stateData = $conversation->state_data ?? [];

        if ($patient) {
            $stateData['patient_name'] = $patient->name;
            $stateData['patient_age'] = $patient->age;
            $stateData['patient_gender'] = $patient->gender;
            $conversation->state_data = $stateData;
            $conversation->current_step = WhatsAppConversation::STEP_AWAITING_CHAMBER;
            $conversation->save();

            $this->promptChamberSelection($conversation, $integration);

            return;
        }

        $conversation->current_step = WhatsAppConversation::STEP_AWAITING_NAME;
        $conversation->state_data = $stateData;
        $conversation->save();

        $text = "👋 Welcome to *{$doctorName}'s Clinic*.\n\nTo schedule your consultation, please reply with your *Full Name* (e.g. Mohammad Rahim).";
        $this->sendMessage($integration, $conversation, $text);
    }

    /**
     * Handle patient full name collection.
     */
    public function handleName(WhatsAppConversation $conversation, WhatsAppIntegration $integration, string $text): void
    {
        $name = trim($text);
        if (mb_strlen($name) < 2) {
            $this->sendMessage($integration, $conversation, '⚠️ Please provide a valid full name (at least 2 letters):');

            return;
        }

        $stateData = $conversation->state_data ?? [];
        $stateData['patient_name'] = $name;
        $stateData['patient_age'] = 30; // default adult age if not specified
        $stateData['patient_gender'] = 'Male';

        $conversation->state_data = $stateData;
        $conversation->current_step = WhatsAppConversation::STEP_AWAITING_CHAMBER;
        $conversation->save();

        $this->promptChamberSelection($conversation, $integration);
    }

    /**
     * Display list of doctor's chambers for patient selection.
     */
    public function promptChamberSelection(WhatsAppConversation $conversation, WhatsAppIntegration $integration): void
    {
        $tenantId = $integration->tenant_id;
        $chambers = Chamber::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->orderBy('is_default', 'desc')
            ->get();

        if ($chambers->isEmpty()) {
            $this->sendMessage($integration, $conversation, '⚠️ No consultation chambers are currently configured for this clinic. Please contact our receptionist.');
            $conversation->resetToIdle();

            return;
        }

        // If only 1 chamber exists, auto-select it and skip to date selection
        if ($chambers->count() === 1) {
            $chamber = $chambers->first();
            $stateData = $conversation->state_data ?? [];
            $stateData['chamber_id'] = $chamber->id;
            $stateData['chamber_name'] = $chamber->name;
            $conversation->state_data = $stateData;
            $conversation->current_step = WhatsAppConversation::STEP_AWAITING_DATE;
            $conversation->save();

            $this->promptDateSelection($conversation, $integration, $chamber);

            return;
        }

        $msg = "🏥 *Select Consultation Chamber:*\n\n";
        foreach ($chambers as $index => $ch) {
            $num = $index + 1;
            $msg .= "*{$num}. {$ch->name}*\n";
            if ($ch->address) {
                $msg .= "   📍 {$ch->address}\n";
            }
            if ($ch->visiting_hours) {
                $msg .= "   ⏰ {$ch->visiting_hours}\n";
            }
            $msg .= "\n";
        }
        $msg .= 'Reply with the *chamber number* (e.g. 1 or 2):';

        $this->sendMessage($integration, $conversation, $msg);
    }

    /**
     * Handle chamber selection response.
     */
    public function handleChamberSelection(WhatsAppConversation $conversation, WhatsAppIntegration $integration, string $text): void
    {
        $tenantId = $integration->tenant_id;
        $chambers = Chamber::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->orderBy('is_default', 'desc')
            ->get();

        $choice = trim($text);
        $selectedChamber = null;

        if (is_numeric($choice)) {
            $idx = (int) $choice - 1;
            if (isset($chambers[$idx])) {
                $selectedChamber = $chambers[$idx];
            }
        } else {
            // Match by name substring
            $selectedChamber = $chambers->first(function ($c) use ($choice) {
                return str_contains(strtolower($c->name), strtolower($choice));
            });
        }

        if (! $selectedChamber) {
            $this->sendMessage($integration, $conversation, '⚠️ Invalid selection. Please reply with the *number* of the chamber (e.g. 1):');

            return;
        }

        $stateData = $conversation->state_data ?? [];
        $stateData['chamber_id'] = $selectedChamber->id;
        $stateData['chamber_name'] = $selectedChamber->name;
        $conversation->state_data = $stateData;
        $conversation->current_step = WhatsAppConversation::STEP_AWAITING_DATE;
        $conversation->save();

        $this->promptDateSelection($conversation, $integration, $selectedChamber);
    }

    /**
     * Prompt patient to select an appointment date.
     */
    public function promptDateSelection(WhatsAppConversation $conversation, WhatsAppIntegration $integration, Chamber $chamber): void
    {
        $todayStr = today()->format('d M (D)');
        $tomorrowStr = today()->addDay()->format('d M (D)');
        $dayAfterStr = today()->addDays(2)->format('d M (D)');

        $msg = "📅 *Select Appointment Date for {$chamber->name}:*\n\n";
        $msg .= "1️⃣ *Today* — {$todayStr}\n";
        $msg .= "2️⃣ *Tomorrow* — {$tomorrowStr}\n";
        $msg .= "3️⃣ *Day After Tomorrow* — {$dayAfterStr}\n\n";
        $msg .= 'Reply with *1*, *2*, *3*, or type a date as *YYYY-MM-DD* (e.g. '.today()->addDays(3)->format('Y-m-d').'):';

        $this->sendMessage($integration, $conversation, $msg);
    }

    /**
     * Handle date selection response.
     */
    public function handleDateSelection(WhatsAppConversation $conversation, WhatsAppIntegration $integration, string $text): void
    {
        $choice = trim(strtolower($text));
        $selectedDate = null;

        if ($choice === '1' || $choice === 'today' || $choice === 'আজ' || $choice === 'আজকে') {
            $selectedDate = today();
        } elseif ($choice === '2' || $choice === 'tomorrow' || $choice === 'কাল' || $choice === 'কালকে') {
            $selectedDate = today()->addDay();
        } elseif ($choice === '3') {
            $selectedDate = today()->addDays(2);
        } else {
            try {
                $parsed = Carbon::parse($choice)->startOfDay();
                if ($parsed->gte(today())) {
                    $selectedDate = $parsed;
                }
            } catch (Exception $e) {
                // Invalid date string
            }
        }

        if (! $selectedDate || $selectedDate->lt(today())) {
            $this->sendMessage($integration, $conversation, "⚠️ Please select a valid upcoming date.\nReply with *1* for Today, *2* for Tomorrow, or format as *YYYY-MM-DD*:");

            return;
        }

        $stateData = $conversation->state_data ?? [];
        $stateData['appointment_date'] = $selectedDate->toDateString();
        $stateData['appointment_date_formatted'] = $selectedDate->format('d M Y (l)');
        $conversation->state_data = $stateData;
        $conversation->current_step = WhatsAppConversation::STEP_AWAITING_SLOT;
        $conversation->save();

        $this->promptSlotSelection($conversation, $integration);
    }

    /**
     * Prompt patient to select an available appointment time slot.
     */
    public function promptSlotSelection(WhatsAppConversation $conversation, WhatsAppIntegration $integration): void
    {
        $stateData = $conversation->state_data ?? [];
        $chamberId = (int) ($stateData['chamber_id'] ?? 0);
        $dateStr = (string) ($stateData['appointment_date'] ?? today()->toDateString());

        $chamber = Chamber::withoutGlobalScope('tenant')->find($chamberId);
        $availableSlots = $this->getAvailableSlots($chamber, Carbon::parse($dateStr), $integration->tenant_id);

        if (empty($availableSlots)) {
            $this->sendMessage($integration, $conversation, "⚠️ All slots for {$stateData['appointment_date_formatted']} are currently fully booked. Please type *2* or a different date to re-select.");
            $conversation->current_step = WhatsAppConversation::STEP_AWAITING_DATE;
            $conversation->save();

            return;
        }

        $msg = "⏰ *Available Visiting Times ({$stateData['appointment_date_formatted']}):*\n\n";
        foreach ($availableSlots as $index => $slot) {
            $num = $index + 1;
            $msg .= "{$num}️⃣ {$slot}\n";
        }
        $msg .= "\nReply with the *time number* (e.g. 1 or 2):";

        $stateData['slots_offered'] = $availableSlots;
        $conversation->state_data = $stateData;
        $conversation->save();

        $this->sendMessage($integration, $conversation, $msg);
    }

    /**
     * Handle slot selection response.
     */
    public function handleSlotSelection(WhatsAppConversation $conversation, WhatsAppIntegration $integration, string $text): void
    {
        $stateData = $conversation->state_data ?? [];
        $slots = $stateData['slots_offered'] ?? [];
        $choice = trim($text);

        $selectedSlot = null;
        if (is_numeric($choice)) {
            $idx = (int) $choice - 1;
            if (isset($slots[$idx])) {
                $selectedSlot = $slots[$idx];
            }
        } else {
            foreach ($slots as $s) {
                if (strtolower($s) === strtolower($choice)) {
                    $selectedSlot = $s;
                    break;
                }
            }
        }

        if (! $selectedSlot) {
            $this->sendMessage($integration, $conversation, '⚠️ Invalid slot choice. Please reply with the number of your preferred time (e.g. 1):');

            return;
        }

        $stateData['appointment_time'] = $selectedSlot;
        $conversation->state_data = $stateData;
        $conversation->current_step = WhatsAppConversation::STEP_AWAITING_CONFIRMATION;
        $conversation->save();

        $doctorName = $integration->tenant?->doctor_name ?: 'Doctor';
        $msg = "📋 *Please Confirm Your Appointment:*\n\n";
        $msg .= "👨‍⚕️ *Doctor:* {$doctorName}\n";
        $msg .= "🏥 *Chamber:* {$stateData['chamber_name']}\n";
        $msg .= "📅 *Date:* {$stateData['appointment_date_formatted']}\n";
        $msg .= "⏰ *Time:* {$selectedSlot}\n";
        $msg .= "👤 *Patient:* {$stateData['patient_name']}\n";
        $msg .= "📱 *Mobile:* {$conversation->wa_id}\n\n";
        $msg .= 'Reply *YES* to confirm, or *CANCEL* to exit.';

        $this->sendMessage($integration, $conversation, $msg);
    }

    /**
     * Handle confirmation response (YES / CANCEL).
     */
    public function handleConfirmation(WhatsAppConversation $conversation, WhatsAppIntegration $integration, string $text): void
    {
        $choice = strtoupper(trim($text));

        if (in_array($choice, ['YES', 'Y', 'CONFIRM', '1', 'হ্যাঁ', 'কনফার্ম'], true)) {
            $stateData = $conversation->state_data ?? [];
            $tenantId = $integration->tenant_id;

            try {
                // Call centralized Unified AppointmentService
                $appointment = $this->appointmentService->createAppointment([
                    'chamber_id' => $stateData['chamber_id'],
                    'appointment_date' => $stateData['appointment_date'],
                    'appointment_time' => $stateData['appointment_time'],
                    'patient_name' => $stateData['patient_name'],
                    'patient_age' => $stateData['patient_age'] ?? 30,
                    'patient_gender' => $stateData['patient_gender'] ?? 'Male',
                    'patient_phone' => $conversation->wa_id,
                    'booking_source' => Appointment::SOURCE_WHATSAPP,
                    'conversation_id' => $conversation->id,
                    'confirmation_status' => 'confirmed',
                ], $tenantId);

                // Link patient_id to conversation if not already linked
                if ($appointment->patient_id && ! $conversation->patient_id) {
                    $conversation->patient_id = $appointment->patient_id;
                }

                $conversation->current_step = WhatsAppConversation::STEP_CONFIRMED;
                $conversation->save();

                $doctorName = $integration->tenant?->doctor_name ?: 'Doctor';
                $chamber = Chamber::withoutGlobalScope('tenant')->find($appointment->chamber_id);

                $confirmMsg = "🎉 *Appointment Confirmed!*\n\n";
                $confirmMsg .= "🎟️ *Token Serial:* #{$appointment->serial_no}\n";
                $confirmMsg .= "🔖 *Booking Ref:* {$appointment->booking_reference}\n\n";
                $confirmMsg .= "👨‍⚕️ *Doctor:* {$doctorName}\n";
                $confirmMsg .= "🏥 *Chamber:* {$chamber?->name}\n";
                if ($chamber?->address) {
                    $confirmMsg .= "📍 *Address:* {$chamber->address}\n";
                }
                $confirmMsg .= "📅 *Date:* {$stateData['appointment_date_formatted']}\n";
                $confirmMsg .= "⏰ *Report Time:* {$appointment->appointment_time}\n\n";
                $confirmMsg .= "💡 Please arrive 15 minutes before your slot and show Serial #{$appointment->serial_no} at the reception counter.\n\n";
                $confirmMsg .= 'Type *MENU* anytime for more options.';

                $this->sendMessage($integration, $conversation, $confirmMsg);
                $conversation->resetToIdle();
            } catch (Exception $e) {
                Log::error('WhatsApp booking creation failed', ['error' => $e->getMessage()]);
                $this->sendMessage($integration, $conversation, "⚠️ We could not confirm this booking: {$e->getMessage()}\nPlease type *MENU* to restart.");
                $conversation->resetToIdle();
            }

            return;
        }

        if (in_array($choice, ['NO', 'CANCEL', '2', 'না', 'বাতিল'], true)) {
            $conversation->resetToIdle();
            $this->sendMessage($integration, $conversation, "❌ Appointment booking cancelled.\n\nType *MENU* whenever you wish to book again.");

            return;
        }

        $this->sendMessage($integration, $conversation, '⚠️ Please reply *YES* to confirm your booking, or *CANCEL* to exit.');
    }

    /**
     * Check status of upcoming active appointment.
     */
    public function handleStatusCheck(WhatsAppConversation $conversation, WhatsAppIntegration $integration): void
    {
        $tenantId = $integration->tenant_id;
        $phone = $conversation->wa_id;

        $appointment = Appointment::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($phone) {
                $suffix = substr($phone, -10);
                $q->where('patient_phone', $phone)
                    ->orWhere('patient_phone', '0'.$suffix)
                    ->orWhere('patient_phone', 'like', '%'.$suffix);
            })
            ->whereDate('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->first();

        if (! $appointment) {
            $this->sendMessage($integration, $conversation, "ℹ️ You do not have any upcoming appointments scheduled at this clinic.\n\nTo schedule a new visit, reply *1* or *BOOK*.");

            return;
        }

        $chamber = Chamber::withoutGlobalScope('tenant')->find($appointment->chamber_id);
        $doctorName = $integration->tenant?->doctor_name ?: 'Doctor';

        $msg = "📋 *Your Upcoming Appointment Details:*\n\n";
        $msg .= "🎟️ *Token Serial:* #{$appointment->serial_no}\n";
        $msg .= "🔖 *Booking Ref:* {$appointment->booking_reference}\n";
        $msg .= "👨‍⚕️ *Doctor:* {$doctorName}\n";
        $msg .= "🏥 *Chamber:* {$chamber?->name}\n";
        $msg .= "📅 *Date:* {$appointment->appointment_date->format('d M Y (l)')}\n";
        $msg .= '⏰ *Time:* '.($appointment->appointment_time ?: 'During visiting hours')."\n";
        $msg .= '📌 *Status:* '.strtoupper($appointment->status)."\n\n";
        $msg .= 'To cancel, reply *CANCEL*. To reschedule, reply *RESCHEDULE*.';

        $this->sendMessage($integration, $conversation, $msg);
    }

    /**
     * Handle patient appointment cancellation.
     */
    public function handleCancellation(WhatsAppConversation $conversation, WhatsAppIntegration $integration): void
    {
        $tenantId = $integration->tenant_id;
        $phone = $conversation->wa_id;

        $appointment = Appointment::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($phone) {
                $suffix = substr($phone, -10);
                $q->where('patient_phone', $phone)
                    ->orWhere('patient_phone', '0'.$suffix)
                    ->orWhere('patient_phone', 'like', '%'.$suffix);
            })
            ->whereDate('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->first();

        if (! $appointment) {
            $this->sendMessage($integration, $conversation, 'ℹ️ No active upcoming appointment was found to cancel.');

            return;
        }

        $appointment->update([
            'status' => 'cancelled',
            'confirmation_status' => 'cancelled',
        ]);

        $this->sendMessage(
            $integration,
            $conversation,
            "✓ Your appointment for {$appointment->appointment_date->format('d M Y')} (Ref: {$appointment->booking_reference}) has been *cancelled*.\n\nReply *BOOK* anytime to schedule a new visit."
        );
        $conversation->resetToIdle();
    }

    /**
     * Calculate available appointment slots based on chamber hours and existing bookings.
     *
     * @return list<string>
     */
    public function getAvailableSlots(?Chamber $chamber, Carbon $date, int $tenantId): array
    {
        $defaultSlots = ['05:00 PM', '05:30 PM', '06:00 PM', '06:30 PM', '07:00 PM', '07:30 PM', '08:00 PM', '08:30 PM'];

        if (! $chamber) {
            return $defaultSlots;
        }

        // Query existing active appointments for this chamber on this date
        $bookedSlots = Appointment::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('chamber_id', $chamber->id)
            ->whereDate('appointment_date', $date->toDateString())
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('appointment_time')
            ->pluck('appointment_time')
            ->toArray();

        $available = [];
        foreach ($defaultSlots as $slot) {
            // If today, filter out past time slots
            if ($date->isToday()) {
                $slotTime = Carbon::createFromTimeString(Carbon::parse($slot)->format('H:i:s'));
                if ($slotTime->lt(now())) {
                    continue;
                }
            }

            // Exclude already booked slot (max 2 patients per 30-min slot)
            $count = count(array_filter($bookedSlots, fn ($s) => strtolower($s) === strtolower($slot)));
            if ($count < 2) {
                $available[] = $slot;
            }
        }

        return ! empty($available) ? $available : $defaultSlots;
    }

    /**
     * Send outbound message via queued job.
     */
    protected function sendMessage(WhatsAppIntegration $integration, WhatsAppConversation $conversation, string $text): void
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
