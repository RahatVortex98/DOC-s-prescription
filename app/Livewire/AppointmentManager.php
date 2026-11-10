<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Chamber;
use App\Models\Tenant;
use App\Services\AppointmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AppointmentManager extends Component
{
    #[Locked]
    public ?int $selected_chamber_id = null;

    public ?Tenant $tenant = null;

    public string $selected_date = '';

    // Booking Form
    public string $patient_name = '';

    public string $patient_phone = '';

    public string $patient_age = '';

    public string $patient_gender = 'Male';

    public string $appointment_time = '';

    public string $notes = '';

    public bool $is_public_portal = false;

    public bool $consent = false;

    public ?string $successMessage = null;

    public ?int $generatedSerial = null;

    public function mount(?Tenant $tenant = null, ?int $chamberId = null): void
    {
        $this->selected_date = today()->toDateString();

        if ($tenant && $tenant->exists) {
            if ($tenant->isSuspended()) {
                abort(403, 'This clinic is currently suspended. Please contact Bikrify support.');
            }

            $this->tenant = $tenant;
            $this->is_public_portal = true;

            $chamberQuery = request()->query('chamber');
            if ($chamberQuery) {
                $found = Chamber::withoutGlobalScope('tenant')
                    ->where('tenant_id', $tenant->id)
                    ->find($chamberQuery);
                if ($found) {
                    $this->selected_chamber_id = $found->id;
                }
            }

            if (! $this->selected_chamber_id) {
                $default = Chamber::withoutGlobalScope('tenant')
                    ->where('tenant_id', $tenant->id)
                    ->where('is_default', true)
                    ->first()
                    ?: Chamber::withoutGlobalScope('tenant')
                        ->where('tenant_id', $tenant->id)
                        ->first();

                $this->selected_chamber_id = $default?->id;
            }
        } else {
            $this->is_public_portal = false;

            if ($chamberId) {
                $chamber = Chamber::findOrFail($chamberId);
                $this->selected_chamber_id = $chamber->id;
            } else {
                $chamber = Chamber::where('is_default', true)->first() ?: Chamber::first();
                $this->selected_chamber_id = $chamber?->id;
            }
        }
    }

    public function getChambersProperty(): Collection
    {
        if ($this->is_public_portal && $this->tenant) {
            return Chamber::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenant->id)
                ->orderBy('name')
                ->get();
        }

        return Chamber::orderBy('name')->get();
    }

    public function selectChamber(int $id): void
    {
        if ($this->is_public_portal && $this->tenant) {
            $chamber = Chamber::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenant->id)
                ->findOrFail($id);
        } else {
            $chamber = Chamber::findOrFail($id);
        }

        $this->selected_chamber_id = $chamber->id;
        $this->resetBookingForm();
    }

    public function resetBookingForm(): void
    {
        $this->patient_name = '';
        $this->patient_phone = '';
        $this->patient_age = '';
        $this->patient_gender = 'Male';
        $this->appointment_time = '';
        $this->notes = '';
        $this->consent = false;
        $this->successMessage = null;
        $this->generatedSerial = null;
    }

    public function bookAppointment(): void
    {
        $rules = [
            'patient_name' => 'required|string|max:150',
            'patient_age' => 'required|numeric|min:0|max:130',
            'patient_gender' => 'required|in:Male,Female,Other',
            'patient_phone' => 'nullable|string|max:20',
            'selected_date' => 'required|date|after_or_equal:today',
        ];

        if ($this->is_public_portal && $this->tenant) {
            $rules['consent'] = 'accepted';
            $rules['selected_chamber_id'] = [
                'required',
                Rule::exists('chambers', 'id')->where('tenant_id', $this->tenant->id),
            ];

            // Rate limiter per IP: max 5 bookings per 60 seconds
            $ipKey = 'public-book:'.request()->ip();
            if (! RateLimiter::attempt($ipKey, 5, function () {}, 60)) {
                $seconds = RateLimiter::availableIn($ipKey);
                $this->addError('booking_limit', "Too many booking requests. Please wait {$seconds} seconds.");

                return;
            }
        } else {
            $rules['selected_chamber_id'] = 'required|exists:chambers,id';
        }

        $this->validate($rules, [
            'consent.accepted' => 'You must agree to the processing of personal data to book an appointment.',
            'selected_date.after_or_equal' => 'Appointment date cannot be in the past.',
        ]);

        $tenantId = $this->is_public_portal && $this->tenant
            ? $this->tenant->id
            : (auth()->check() ? auth()->user()->tenant_id : null);

        $bookingSource = $this->is_public_portal
            ? Appointment::SOURCE_QR
            : Appointment::SOURCE_RECEPTIONIST;

        $appointment = app(AppointmentService::class)->createAppointment([
            'chamber_id' => $this->selected_chamber_id,
            'appointment_date' => $this->selected_date,
            'appointment_time' => $this->appointment_time,
            'patient_name' => $this->patient_name,
            'patient_age' => (int) $this->patient_age,
            'patient_gender' => $this->patient_gender,
            'patient_phone' => $this->patient_phone,
            'notes' => $this->notes,
            'booking_source' => $bookingSource,
        ], $tenantId);

        $this->generatedSerial = $appointment->serial_no;
        $this->successMessage = "Appointment confirmed! Serial #{$appointment->serial_no} for {$this->patient_name}.";

        $this->patient_name = '';
        $this->patient_phone = '';
        $this->patient_age = '';
        $this->notes = '';
        $this->consent = false;
    }

    public function updateStatus(int $appointmentId, string $status): void
    {
        $appointment = Appointment::findOrFail($appointmentId);
        if (in_array($status, ['waiting', 'with_doctor', 'completed', 'cancelled'])) {
            $appointment->update(['status' => $status]);
        }
    }

    public function deleteAppointment(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);
        AuditLog::record('appointment.deleted', $appointment, [
            'serial_no' => $appointment->serial_no,
            'patient_name' => $appointment->patient_name,
            'appointment_date' => $appointment->appointment_date?->toDateString(),
        ]);
        $appointment->delete();
    }

    public function render(): View
    {
        if ($this->is_public_portal && $this->tenant) {
            $activeChamber = $this->selected_chamber_id
                ? Chamber::withoutGlobalScope('tenant')->where('tenant_id', $this->tenant->id)->find($this->selected_chamber_id)
                : null;

            $todayAppointments = Appointment::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenant->id)
                ->where('chamber_id', $this->selected_chamber_id)
                ->whereDate('appointment_date', $this->selected_date)
                ->orderBy('serial_no')
                ->get();
        } else {
            $activeChamber = Chamber::find($this->selected_chamber_id);

            $todayAppointments = Appointment::where('chamber_id', $this->selected_chamber_id)
                ->whereDate('appointment_date', $this->selected_date)
                ->orderBy('serial_no')
                ->get();
        }

        $stats = [
            'total' => $todayAppointments->count(),
            'waiting' => $todayAppointments->where('status', 'waiting')->count(),
            'with_doctor' => $todayAppointments->where('status', 'with_doctor')->count(),
            'completed' => $todayAppointments->where('status', 'completed')->count(),
        ];

        return view('livewire.appointment-manager', [
            'chambers' => $this->chambers,
            'activeChamber' => $activeChamber,
            'appointments' => $todayAppointments,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
