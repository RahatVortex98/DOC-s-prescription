<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <span>🛡️</span> Clinical Audit Trail & Access Logs
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Immutable tenant-scoped security logs tracking all prescription creation, printing, voiding, and appointment modifications.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('doctor.dashboard') }}" class="text-xs bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg font-bold transition">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Filter by Action</label>
                <select wire:model.live="action_filter" class="text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-white text-slate-800">
                    <option value="">All Audit Actions</option>
                    <option value="prescription.created">Prescription Created</option>
                    <option value="prescription.printed">Prescription Printed</option>
                    <option value="prescription.pdf_downloaded">PDF Downloaded</option>
                    <option value="prescription.voided">Prescription Voided</option>
                    <option value="prescription.shared_viewed">Shared Prescription Viewed</option>
                    <option value="appointment.deleted">Appointment Deleted</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Filter by Date</label>
                <input type="date" wire:model.live="date_filter" class="text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-white text-slate-800">
            </div>
            @if($action_filter || $date_filter)
                <div class="self-end">
                    <button type="button" wire:click="clearFilters" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1.5">
                        Clear Filters
                    </button>
                </div>
            @endif
        </div>
        <div class="text-xs text-slate-500">
            Showing <strong class="text-slate-800">{{ $logs->total() }}</strong> logged events
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                <tr>
                    <th class="py-3 px-4">Timestamp</th>
                    <th class="py-3 px-4">Action</th>
                    <th class="py-3 px-4">User</th>
                    <th class="py-3 px-4">IP Address</th>
                    <th class="py-3 px-4">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap font-mono text-[11px]">
                            {{ $log->created_at->format('d M, Y h:i:s A') }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if(str_contains($log->action, 'created'))
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-bold text-[10px]">
                                    {{ $log->action }}
                                </span>
                            @elseif(str_contains($log->action, 'voided') || str_contains($log->action, 'deleted'))
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-bold text-[10px]">
                                    {{ $log->action }}
                                </span>
                            @else
                                <span class="bg-cyan-50 text-cyan-700 border border-cyan-200 px-2.5 py-0.5 rounded-full font-bold text-[10px]">
                                    {{ $log->action }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800 whitespace-nowrap">
                            {{ $log->user?->name ?? 'System / Public Guest' }}
                            @if($log->user?->role)
                                <span class="text-[10px] text-slate-400 font-normal">({{ ucfirst($log->user->role) }})</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                            {{ $log->ip ?: '—' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                            @if($log->details)
                                {{ json_encode($log->details) }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                            No audit log records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($logs->hasPages())
            <div class="p-3 border-t bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
