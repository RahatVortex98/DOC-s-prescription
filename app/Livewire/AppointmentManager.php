<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class AppointmentManager extends Component
{
    public ?int $selected_chamber_id = null;

    public string $selected_date = '';

    // Booking Form
    public string $patient_name = '';

    public string $patient_phone = '';

    public string $patient_age = '';

    public string $patient_gender = 'Male';

    public string $appointment_time = '';

    public string $notes = '';

    public bool $is_public_portal = false;

    public ?string $successMessage = null;

    public ?int $generatedSerial = null;

    public function mount(?int $chamberId = null): void
    {
        $this->is_public_portal = request()->routeIs('appointment.public_book');
        $this->selected_date = today()->toDateString();

        if ($chamberId) {
            $this->selected_chamber_id = $chamberId;
        } else {
            $chamber = Chamber::where('is_default', true)->first() ?: Chamber::first();
            $this->selected_chamber_id = $chamber ? $chamber->id : null;
        }
    }

    public function getChambersProperty(): Collection
    {
        return Chamber::orderBy('name')->get();
    }

    public function selectChamber(int $id): void
    {
        $this->selected_chamber_id = $id;
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
        $this->successMessage = null;
        $this->generatedSerial = null;
    }

    public function bookAppointment(): void
    {
        $this->validate([
            'selected_chamber_id' => 'required|exists:chambers,id',
            'patient_name' => 'required|string|max:150',
            'patient_age' => 'required|numeric|min:0|max:130',
            'patient_gender' => 'required|in:Male,Female,Other',
            'patient_phone' => 'nullable|string|max:20',
            'selected_date' => 'required|date',
        ]);

        // Auto calculate next serial number for this chamber on this date
        $lastSerial = Appointment::where('chamber_id', $this->selected_chamber_id)
            ->whereDate('appointment_date', $this->selected_date)
            ->max('serial_no') ?? 0;

        $serial = $lastSerial + 1;

        // Associate or find patient if phone exists
        $patientId = null;
        $phone = trim($this->patient_phone);
        if ($phone !== '') {
            $patient = Patient::where('phone', $phone)->first();
            if ($patient) {
                $patientId = $patient->id;
            } else {
                $newPatient = Patient::create([
                    'patient_id' => 'PID-'.strtoupper(bin2hex(random_bytes(3))),
                    'name' => $this->patient_name,
                    'phone' => $phone,
                    'age' => (int) $this->patient_age,
                    'gender' => $this->patient_gender,
                ]);
                $patientId = $newPatient->id;
            }
        }

        $appointment = Appointment::create([
            'chamber_id' => $this->selected_chamber_id,
            'patient_id' => $patientId,
            'serial_no' => $serial,
            'patient_name' => $this->patient_name,
            'patient_phone' => $phone ?: null,
            'patient_age' => (int) $this->patient_age,
            'patient_gender' => $this->patient_gender,
            'appointment_date' => $this->selected_date,
            'appointment_time' => $this->appointment_time ?: null,
            'status' => 'waiting',
            'notes' => $this->notes ?: null,
        ]);

        $this->generatedSerial = $serial;
        $this->successMessage = "Appointment confirmed! Serial #{$serial} for {$this->patient_name}.";

        $this->patient_name = '';
        $this->patient_phone = '';
        $this->patient_age = '';
        $this->notes = '';
    }

    public function updateStatus(int $appointmentId, string $status): void
    {
        $appointment = Appointment::find($appointmentId);
        if ($appointment && in_array($status, ['waiting', 'with_doctor', 'completed', 'cancelled'])) {
            $appointment->update(['status' => $status]);
        }
    }

    public function deleteAppointment(int $appointmentId): void
    {
        Appointment::destroy($appointmentId);
    }

    public function render(): View
    {
        $activeChamber = Chamber::find($this->selected_chamber_id);

        $todayAppointments = Appointment::where('chamber_id', $this->selected_chamber_id)
            ->whereDate('appointment_date', $this->selected_date)
            ->orderBy('serial_no')
            ->get();

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
