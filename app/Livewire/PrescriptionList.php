<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Chamber;
use App\Models\Prescription;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PrescriptionList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $chamber_filter = null;

    public string $date_filter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedChamberFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->chamber_filter = null;
        $this->date_filter = '';
        $this->resetPage();
    }

    public function voidPrescription(int $id, string $reason = 'Voided by doctor'): void
    {
        $prescription = Prescription::findOrFail($id);
        $prescription->void($reason);

        AuditLog::record('prescription.voided', $prescription, [
            'rx_no' => $prescription->prescription_no,
            'reason' => $reason,
        ]);
    }

    public function deletePrescription(int $id): void
    {
        $prescription = Prescription::findOrFail($id);
        AuditLog::record('prescription.deleted', $prescription, [
            'rx_no' => $prescription->prescription_no,
        ]);
        $prescription->delete();
    }

    public function render(): View
    {
        $query = Prescription::query()
            ->with(['patient', 'chamber', 'items'])
            ->latest('id');

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('prescription_no', 'like', "%{$term}%")
                    ->orWhere('diagnosis', 'like', "%{$term}%")
                    ->orWhereHas('patient', function ($pq) use ($term) {
                        $pq->where('name', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%")
                            ->orWhere('patient_id', 'like', "%{$term}%");
                    });
            });
        }

        if ($this->chamber_filter) {
            $query->where('chamber_id', $this->chamber_filter);
        }

        if ($this->date_filter !== '') {
            $query->whereDate('prescription_date', $this->date_filter);
        }

        $prescriptions = $query->paginate(12);

        $todayCount = Prescription::whereDate('created_at', today())->count();
        $totalCount = Prescription::count();
        $chambers = Chamber::orderBy('name')->get();

        return view('livewire.prescription-list', [
            'prescriptions' => $prescriptions,
            'todayCount' => $todayCount,
            'totalCount' => $totalCount,
            'chambers' => $chambers,
        ])->layout('layouts.app');
    }
}
