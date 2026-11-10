<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DoctorDashboard extends Component
{
    public function render(): View
    {
        $today = today();
        $thisMonth = today()->startOfMonth();

        $stats = [
            'total_prescriptions' => Prescription::count(),
            'prescriptions_today' => Prescription::whereDate('prescription_date', $today)->count(),
            'prescriptions_this_month' => Prescription::where('prescription_date', '>=', $thisMonth)->count(),
            'total_patients' => Patient::count(),
            'appointments_today' => Appointment::whereDate('appointment_date', $today)->count(),
            'active_chambers' => Chamber::count(),
        ];

        // Top Diagnoses
        $topDiagnoses = Prescription::select('diagnosis', DB::raw('count(*) as count'))
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->groupBy('diagnosis')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Top Medicines
        $topMedicines = PrescriptionItem::whereHas('prescription')
            ->select('medicine_name', 'type', DB::raw('count(*) as count'))
            ->groupBy('medicine_name', 'type')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Prescriptions by Chamber
        $chamberStats = Chamber::withCount('prescriptions')->get();

        // Gender Distribution
        $genderStats = Patient::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->pluck('count', 'gender')
            ->toArray();

        // Booking Channels Breakdown
        $totalBookings = Appointment::count();
        $channelStats = [
            'total' => $totalBookings,
            'whatsapp' => Appointment::where('booking_source', Appointment::SOURCE_WHATSAPP)->count(),
            'qr' => Appointment::where('booking_source', Appointment::SOURCE_QR)->count(),
            'receptionist' => Appointment::where('booking_source', Appointment::SOURCE_RECEPTIONIST)->count(),
        ];

        // Recent Prescriptions
        $recentPrescriptions = Prescription::with(['patient', 'chamber'])
            ->latest('id')
            ->limit(8)
            ->get();

        return view('livewire.doctor-dashboard', [
            'stats' => $stats,
            'channelStats' => $channelStats,
            'topDiagnoses' => $topDiagnoses,
            'topMedicines' => $topMedicines,
            'chamberStats' => $chamberStats,
            'genderStats' => $genderStats,
            'recentPrescriptions' => $recentPrescriptions,
        ])->layout('layouts.app');
    }
}
