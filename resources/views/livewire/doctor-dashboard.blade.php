<div class="max-w-7xl mx-auto py-6 px-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b mb-6">
        <div>
            <h1 class="text-2xl font-black text-cyan-800">Doctor Practice Analytics</h1>
            <p class="text-xs text-gray-500 mt-1">Real-time overview of consultations, patient demographics, and top prescribed treatments</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('prescription.builder') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-1.5">
                <span>℞</span> New Prescription
            </a>
            <a href="{{ route('appointment.manager') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2 px-4 rounded-lg border transition flex items-center gap-1.5">
                <span>👥</span> Queue Desk
            </a>
        </div>
    </div>

    <!-- Stat Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="text-[11px] font-bold text-gray-400 uppercase">Today's Prescriptions</div>
            <div class="text-2xl font-black text-cyan-800 mt-1">{{ $stats['prescriptions_today'] }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">This Month: <strong>{{ $stats['prescriptions_this_month'] }}</strong></div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="text-[11px] font-bold text-gray-400 uppercase">Total Prescriptions</div>
            <div class="text-2xl font-black text-gray-800 mt-1">{{ $stats['total_prescriptions'] }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">All time consultations</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="text-[11px] font-bold text-gray-400 uppercase">Registered Patients</div>
            <div class="text-2xl font-black text-emerald-700 mt-1">{{ $stats['total_patients'] }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">With phone / history</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="text-[11px] font-bold text-gray-400 uppercase">Practice Chambers</div>
            <div class="text-2xl font-black text-indigo-700 mt-1">{{ $stats['active_chambers'] }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Multi-hospital seating</div>
        </div>
    </div>

    <!-- Analytics Breakdown Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Top Diagnoses -->
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <h2 class="font-bold text-xs uppercase text-gray-700 border-b pb-2 mb-3 flex items-center gap-1.5">
                <span>🩺</span> Top Clinical Diagnoses
            </h2>
            <div class="space-y-3">
                @forelse($topDiagnoses as $diag)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-semibold text-gray-800 truncate max-w-[200px]">{{ $diag->diagnosis }}</span>
                            <span class="font-bold text-cyan-800">{{ $diag->count }} cases</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            @php
                                $diagPct = $stats['total_prescriptions'] > 0 ? min(100, round(($diag->count / $stats['total_prescriptions']) * 100)) : 0;
                            @endphp
                            <div class="bg-cyan-600 h-1.5 rounded-full" style="width: {{ $diagPct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 text-xs">No diagnosis records yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Top Prescribed Medicines -->
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <h2 class="font-bold text-xs uppercase text-gray-700 border-b pb-2 mb-3 flex items-center gap-1.5">
                <span>💊</span> Top Prescribed Medicines
            </h2>
            <div class="space-y-2.5">
                @forelse($topMedicines as $med)
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-gray-50 border">
                        <div>
                            <span class="font-bold text-gray-800">{{ $med->type }} {{ $med->medicine_name }}</span>
                        </div>
                        <span class="bg-cyan-100 text-cyan-800 text-[11px] font-black px-2 py-0.5 rounded-full">
                            {{ $med->count }}x
                        </span>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 text-xs">No prescribed medicine records yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Practice by Hospital / Chamber -->
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <h2 class="font-bold text-xs uppercase text-gray-700 border-b pb-2 mb-3 flex items-center gap-1.5">
                <span>🏥</span> Practice by Hospital / Chamber
            </h2>
            <div class="space-y-3">
                @foreach($chamberStats as $cStat)
                    <div class="p-2.5 rounded-lg border bg-slate-50 text-xs">
                        <div class="flex items-center justify-between font-bold text-gray-800">
                            <span class="truncate max-w-[180px]">{{ $cStat->name }}</span>
                            <span class="text-cyan-800">{{ $cStat->prescriptions_count }} rx</span>
                        </div>
                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $cStat->visiting_hours ?: 'Regular' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Prescriptions Table -->
    <div class="bg-white rounded-xl shadow-sm border p-4">
        <div class="flex items-center justify-between border-b pb-3 mb-3">
            <h2 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                <span>📄</span> Recent Prescriptions
            </h2>
            <a href="{{ route('prescription.list') }}" class="text-xs text-cyan-600 hover:text-cyan-800 font-bold underline">
                View All Records →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-gray-500 uppercase border-b">
                    <tr>
                        <th class="p-2.5">Rx No & Date</th>
                        <th class="p-2.5">Patient Details</th>
                        <th class="p-2.5">Hospital / Chamber</th>
                        <th class="p-2.5">Diagnosis</th>
                        <th class="p-2.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentPrescriptions as $rRx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-2.5">
                                <span class="font-bold text-cyan-800">{{ $rRx->prescription_no }}</span>
                                <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($rRx->prescription_date)->format('d M, Y') }}</div>
                            </td>
                            <td class="p-2.5">
                                <div class="font-bold text-gray-800">{{ $rRx->patient->name }}</div>
                                <div class="text-[10px] text-gray-400">{{ $rRx->patient->age }}Y, {{ $rRx->patient->gender }}</div>
                            </td>
                            <td class="p-2.5 font-medium text-gray-700">
                                {{ $rRx->chamber_name }}
                            </td>
                            <td class="p-2.5">
                                <span class="bg-amber-50 text-amber-800 border border-amber-200 px-2 py-0.5 rounded text-[11px] font-semibold">
                                    {{ $rRx->diagnosis ?: 'General Checkup' }}
                                </span>
                            </td>
                            <td class="p-2.5 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('prescription.print', $rRx->id) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-2 py-1 rounded text-[11px] font-bold">
                                    Print A4
                                </a>
                                <a href="{{ route('prescription.pdf', $rRx->id) }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-2 py-1 rounded text-[11px] font-bold">
                                    PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-400">No recent prescriptions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
