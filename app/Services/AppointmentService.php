<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AppointmentService
{
    /**
     * Create an appointment across any booking channel (receptionist, qr, whatsapp).
     *
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    public function createAppointment(array $data, ?int $tenantId = null): Appointment
    {
        // 1. Resolve Tenant
        $resolvedTenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : null);
        if (! $resolvedTenantId) {
            throw new InvalidArgumentException('A valid tenant must be provided or authenticated to create an appointment.');
        }

        // 2. Validate Chamber Ownership
        $chamberId = (int) ($data['chamber_id'] ?? 0);
        $chamber = Chamber::withoutGlobalScope('tenant')
            ->where('tenant_id', $resolvedTenantId)
            ->where('id', $chamberId)
            ->first();

        if (! $chamber) {
            throw new InvalidArgumentException("Chamber [{$chamberId}] does not belong to clinic tenant [{$resolvedTenantId}].");
        }

        // 3. Validate Date (Cannot be past date)
        $dateStr = (string) ($data['appointment_date'] ?? '');
        if (empty($dateStr)) {
            throw new InvalidArgumentException('Appointment date is required.');
        }

        $appointmentDate = Carbon::parse($dateStr)->startOfDay();
        if ($appointmentDate->lt(today()->startOfDay())) {
            throw new InvalidArgumentException('Appointment date cannot be in the past.');
        }

        // 4. Sanitize and Extract Patient Info
        $patientName = trim((string) ($data['patient_name'] ?? ''));
        if (empty($patientName)) {
            throw new InvalidArgumentException('Patient name is required.');
        }

        $patientAge = (int) ($data['patient_age'] ?? 0);
        $patientGender = (string) ($data['patient_gender'] ?? 'Male');
        if (! in_array($patientGender, ['Male', 'Female', 'Other'], true)) {
            $patientGender = 'Male';
        }

        $patientPhone = trim((string) ($data['patient_phone'] ?? ''));
        $appointmentTime = ! empty($data['appointment_time']) ? trim((string) $data['appointment_time']) : null;
        $notes = ! empty($data['notes']) ? trim((string) $data['notes']) : null;
        $bookingSource = (string) ($data['booking_source'] ?? Appointment::SOURCE_RECEPTIONIST);
        $confirmationStatus = (string) ($data['confirmation_status'] ?? 'confirmed');
        $conversationId = isset($data['conversation_id']) ? (int) $data['conversation_id'] : null;

        // 5. Execute Creation Inside Database Transaction with Lock
        return DB::transaction(function () use (
            $resolvedTenantId,
            $chamberId,
            $appointmentDate,
            $patientName,
            $patientAge,
            $patientGender,
            $patientPhone,
            $appointmentTime,
            $notes,
            $bookingSource,
            $confirmationStatus,
            $conversationId,
            $data
        ) {
            // Patient Disambiguation & Linking
            $patientId = isset($data['patient_id']) ? (int) $data['patient_id'] : null;

            if ($patientId) {
                // Verify provided patient belongs to tenant
                $existingPatient = Patient::withoutGlobalScope('tenant')
                    ->where('tenant_id', $resolvedTenantId)
                    ->where('id', $patientId)
                    ->first();

                if (! $existingPatient) {
                    $patientId = null;
                }
            }

            if (! $patientId && $patientPhone !== '') {
                $matchedPatient = Patient::withoutGlobalScope('tenant')
                    ->where('tenant_id', $resolvedTenantId)
                    ->where('phone', $patientPhone)
                    ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($patientName)])
                    ->first();

                if ($matchedPatient) {
                    $patientId = $matchedPatient->id;
                } else {
                    $newPatient = new Patient([
                        'patient_id' => 'PID-'.strtoupper(bin2hex(random_bytes(3))),
                        'name' => $patientName,
                        'phone' => $patientPhone,
                        'age' => $patientAge,
                        'gender' => $patientGender,
                    ]);
                    $newPatient->tenant_id = $resolvedTenantId;
                    $newPatient->save();
                    $patientId = $newPatient->id;
                }
            }

            // Lock and Generate Atomic Serial Number
            $lastSerial = Appointment::withoutGlobalScope('tenant')
                ->where('chamber_id', $chamberId)
                ->whereDate('appointment_date', $appointmentDate->toDateString())
                ->lockForUpdate()
                ->max('serial_no') ?? 0;

            $serialNo = $lastSerial + 1;

            // Generate Collision-Safe Unique Booking Reference
            $bookingReference = $this->generateUniqueBookingReference($resolvedTenantId);

            $appointment = new Appointment([
                'chamber_id' => $chamberId,
                'patient_id' => $patientId,
                'serial_no' => $serialNo,
                'patient_name' => $patientName,
                'patient_phone' => $patientPhone !== '' ? $patientPhone : null,
                'patient_age' => $patientAge,
                'patient_gender' => $patientGender,
                'appointment_date' => $appointmentDate->toDateString(),
                'appointment_time' => $appointmentTime,
                'status' => 'waiting',
                'notes' => $notes,
                'booking_source' => $bookingSource,
                'booking_reference' => $bookingReference,
                'confirmation_status' => $confirmationStatus,
                'conversation_id' => $conversationId,
            ]);

            $appointment->tenant_id = $resolvedTenantId;
            $appointment->save();

            return $appointment;
        }, 5);
    }

    /**
     * Generate an alphanumeric booking reference (e.g. BK-7A9B1C) unique per tenant.
     */
    protected function generateUniqueBookingReference(int $tenantId): string
    {
        do {
            $reference = 'BK-'.strtoupper(Str::random(6));
            $exists = Appointment::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->where('booking_reference', $reference)
                ->exists();
        } while ($exists);

        return $reference;
    }
}
