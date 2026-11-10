<?php

namespace App\Livewire;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $action_filter = '';

    public string $date_filter = '';

    public function updatedActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->action_filter = '';
        $this->date_filter = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $query = AuditLog::with('user')
            ->latest('id');

        if (! empty($this->action_filter)) {
            $query->where('action', $this->action_filter);
        }

        if (! empty($this->date_filter)) {
            $query->whereDate('created_at', $this->date_filter);
        }

        $logs = $query->paginate(20);

        return view('livewire.audit-log-viewer', [
            'logs' => $logs,
        ])->layout('layouts.app');
    }
}
