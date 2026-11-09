<div class="max-w-7xl mx-auto py-6 px-4">
    <!-- Header with Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-cyan-800">Prescription Records</h1>
                <span class="text-xs bg-cyan-100 text-cyan-800 font-bold px-2.5 py-0.5 rounded-full">
                    Total: {{ $totalCount }} | Today: {{ $todayCount }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Search past patient visits, reprint prescriptions, or export PDF</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('prescription.builder') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold py-2.5 px-5 rounded-lg shadow-sm transition flex items-center gap-1.5">
                <span>+</span> New Prescription
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-5">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Search Patient / Rx / Phone / Diagnosis</label>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white"
                       placeholder="e.g. Rahim, 01711..., RX-2026..., or CKD">
            </div>
            <div class="sm:col-span-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Filter by Hospital / Chamber</label>
                <select wire:model.live="chamber_filter" class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white">
                    <option value="">All Hospitals & Chambers</option>
                    @foreach($chambers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Date</label>
                <input type="date" wire:model.live="date_filter" class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white">
            </div>
            <div class="sm:col-span-1">
                @if($search || $chamber_filter || $date_filter)
                    <button type="button" wire:click="clearFilters" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs py-2 px-2 rounded font-semibold border transition text-center">
                        Reset
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-gray-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Rx No & Date</th>
                        <th class="p-3">Patient Details</th>
                        <th class="p-3">Hospital / Chamber</th>
                        <th class="p-3">Diagnosis</th>
                        <th class="p-3">Medicines</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($prescriptions as $rx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3">
                                <span class="font-bold text-cyan-800">{{ $rx->prescription_no }}</span>
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($rx->prescription_date)->format('d M, Y') }}
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-gray-800">{{ $rx->patient->name }}</div>
                                <div class="text-[11px] text-gray-400">
                                    {{ $rx->patient->age }}Y, {{ $rx->patient->gender }} | {{ $rx->patient->phone ?: 'No phone' }}
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="font-medium text-gray-700">{{ $rx->chamber_name }}</span>
                                <div class="text-[10px] text-gray-400 truncate max-w-xs">{{ $rx->doctor_name }}</div>
                            </td>
                            <td class="p-3">
                                @if(auth()->user()?->isAssistant())
                                    <span class="text-slate-400 italic text-[11px]">[Restricted]</span>
                                @else
                                    <span class="inline-block bg-amber-50 text-amber-800 border border-amber-200 rounded px-2 py-0.5 font-semibold text-[11px]">
                                        {{ $rx->diagnosis ?: 'General Checkup' }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-bold">
                                    {{ $rx->items->count() }} item(s)
                                </span>
                            </td>
                            <td class="p-3 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('prescription.builder') }}?clone={{ $rx->id }}" class="inline-block bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 px-2 py-1 rounded text-xs font-bold transition" title="Clone / Re-use in Studio">
                                    ↻ Re-use
                                </a>
                                @if($rx->investigations)
                                    <a href="{{ route('prescription.investigations', $rx->id) }}" target="_blank" class="inline-block bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-2 py-1 rounded text-xs font-bold transition" title="Print Lab Slip">
                                        🧪 Lab
                                    </a>
                                @endif
                                <a href="{{ route('prescription.print', $rx->id) }}" target="_blank" class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-800 px-2 py-1 rounded text-xs font-bold transition">
                                    Print A4
                                </a>
                                <a href="{{ route('prescription.pdf', $rx->id) }}" class="inline-block bg-cyan-600 hover:bg-cyan-700 text-white px-2 py-1 rounded text-xs font-bold transition">
                                    PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">
                                No prescriptions found matching your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($prescriptions->hasPages())
            <div class="p-4 border-t bg-gray-50">
                {{ $prescriptions->links() }}
            </div>
        @endif
    </div>
</div>
