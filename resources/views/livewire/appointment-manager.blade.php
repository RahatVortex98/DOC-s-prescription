<div class="max-w-7xl mx-auto py-6 px-4">
    @if($is_public_portal)
        <!-- Public Patient Self-Booking Portal Mode -->
        <div class="max-w-xl mx-auto space-y-5">
            <!-- Doctor & Chamber Header Card -->
            <div class="bg-gradient-to-r from-[#173087] via-[#1e3fae] to-[#0f246b] text-white rounded-2xl p-6 shadow-xl">
                @if($activeChamber)
                    <div class="flex items-center gap-2 mb-2">
                        <img src="{{ asset('images/bikrify-logo.png') }}" class="w-6 h-6 rounded-md">
                        <span class="bg-[#ff6200] text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                            {{ $activeChamber->specialty }}
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-white">{{ $activeChamber->doctor_name }}</h1>
                    <p class="text-xs text-blue-100 mt-0.5">{{ $activeChamber->doctor_qualification }}</p>
                    <p class="text-xs text-orange-200 font-semibold mt-0.5">{{ $activeChamber->doctor_designation }}</p>

                    <div class="mt-4 pt-3 border-t border-blue-800 text-xs text-blue-100 space-y-1">
                        <div><strong>🏥 Hospital:</strong> {{ $activeChamber->name }}</div>
                        @if($activeChamber->room_number)
                            <div><strong>Room / Chamber:</strong> {{ $activeChamber->room_number }}</div>
                        @endif
                        <div><strong>📍 Address:</strong> {{ $activeChamber->address }}</div>
                        <div><strong>⏰ Visiting Hours:</strong> {{ $activeChamber->visiting_hours }}</div>
                        @if($activeChamber->whatsapp_number)
                            <div class="pt-1">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $activeChamber->whatsapp_number) }}" target="_blank" class="inline-flex items-center gap-1 bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-1 rounded-full text-[11px] font-bold">
                                    💬 WhatsApp Hotline: {{ $activeChamber->whatsapp_number }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Serial Token Generated Confirmation Card -->
            @if($generatedSerial && $successMessage)
                <div class="bg-emerald-50 border-2 border-emerald-400 rounded-2xl p-6 text-center shadow-lg animate-bounce-once">
                    <div class="w-16 h-16 bg-emerald-600 text-white rounded-full flex items-center justify-center mx-auto text-2xl font-black shadow-md">
                        #{{ $generatedSerial }}
                    </div>
                    <h2 class="text-lg font-black text-emerald-900 mt-3">Serial Token Issued!</h2>
                    <p class="text-xs text-emerald-700 mt-1">{{ $successMessage }}</p>
                    <div class="mt-4 bg-white border border-emerald-200 rounded-xl p-3 text-xs text-gray-700 text-left space-y-1">
                        <div><strong>Doctor:</strong> {{ $activeChamber->doctor_name ?? 'Doctor' }}</div>
                        <div><strong>Hospital:</strong> {{ $activeChamber->name ?? 'Chamber' }} ({{ $activeChamber->room_number ?? 'OPD' }})</div>
                        <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($selected_date)->format('d M, Y') }}</div>
                        <div><strong>Your Serial:</strong> <span class="text-emerald-700 font-black text-sm">#{{ $generatedSerial }}</span></div>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-3">
                        Please arrive at the chamber before your serial number is called.
                    </p>
                    <button type="button" wire:click="resetBookingForm" class="mt-4 text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-lg transition">
                        Book Another Appointment
                    </button>
                </div>
            @else
                <!-- Self Booking Form -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h2 class="font-black text-base text-gray-800 mb-1">Get Your Serial Token</h2>
                    <p class="text-xs text-gray-500 mb-4">Book your consultation with {{ $activeChamber->doctor_name ?? 'the Doctor' }}</p>

                    <form wire:submit.prevent="bookAppointment" class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-700 uppercase mb-1">Appointment Date *</label>
                            <input type="date" wire:model.live="selected_date" class="w-full border rounded-lg p-2.5 text-xs bg-gray-50 focus:bg-white">
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 uppercase mb-1">Patient Full Name *</label>
                            <input type="text" wire:model="patient_name" class="w-full border rounded-lg p-2.5 text-xs bg-gray-50 focus:bg-white" placeholder="e.g. Rahim Ahmed">
                            @error('patient_name') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 uppercase mb-1">Age (Years) *</label>
                                <input type="number" wire:model="patient_age" class="w-full border rounded-lg p-2.5 text-xs bg-gray-50 focus:bg-white" placeholder="42">
                                @error('patient_age') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 uppercase mb-1">Gender *</label>
                                <select wire:model="patient_gender" class="w-full border rounded-lg p-2.5 text-xs bg-gray-50 focus:bg-white">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 uppercase mb-1">Mobile Phone Number *</label>
                            <input type="text" wire:model="patient_phone" class="w-full border rounded-lg p-2.5 text-xs bg-gray-50 focus:bg-white" placeholder="017xxxxxxxx">
                            <span class="text-[10px] text-gray-400">Your serial confirmation can be sent to this number.</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 uppercase mb-1">Brief Symptoms / Medical Problem</label>
                            <textarea wire:model="notes" rows="2" class="w-full border rounded-lg p-2 text-xs bg-gray-50 focus:bg-white" placeholder="e.g. High fever, blood pressure checkup, kidney consultation"></textarea>
                        </div>

                        <div>
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="consent" class="mt-0.5 rounded border-slate-300 text-cyan-700 focus:ring-cyan-600">
                                <span class="text-[11px] text-gray-600">I agree to the processing of my personal data for appointment scheduling. *</span>
                            </label>
                            @error('consent') <span class="text-rose-600 text-[11px] block mt-0.5 font-medium">{{ $message }}</span> @enderror
                        </div>

                        @error('booking_limit')
                            <div class="p-2.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs font-semibold">
                                {{ $message }}
                            </div>
                        @enderror

                        <button type="submit" class="w-full bg-cyan-700 hover:bg-cyan-800 text-white font-black py-3 px-4 rounded-xl text-xs shadow-md transition cursor-pointer">
                            Confirm Appointment & Get Serial Token
                        </button>
                    </form>
                </div>
            @endif

            <!-- Live Serial Tracker (Privacy-Protected) -->
            <div class="bg-white rounded-2xl shadow-sm border p-4 text-xs">
                <div class="flex items-center justify-between border-b pb-2 mb-3">
                    <span class="font-bold text-gray-700 uppercase">Live Queue Status for Today</span>
                    <span class="text-[11px] text-cyan-700 font-bold">Total Tokens: {{ count($appointments) }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-center">
                    <div class="p-3 bg-cyan-50 border border-cyan-200 rounded-xl">
                        <span class="block text-[11px] text-cyan-800 font-semibold uppercase">Currently Inside</span>
                        @php
                            $curr = $appointments->where('status', 'with_doctor')->first();
                        @endphp
                        <span class="text-xl font-black text-cyan-900">
                            {{ $curr ? '#' . $curr->serial_no : 'Waiting' }}
                        </span>
                    </div>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <span class="block text-[11px] text-amber-800 font-semibold uppercase">Patients In Queue</span>
                        <span class="text-xl font-black text-amber-900">
                            {{ $appointments->where('status', 'waiting')->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Receptionist & Doctor Queue Desk Mode -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b mb-6">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-cyan-800">
                        Receptionist & Appointment Queue Desk
                    </h1>
                    <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-cyan-100 text-cyan-800">
                        Bikrify Queue
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Manage daily patient appointments, auto-generate serial tokens, and sync directly with the Doctor's Prescription Studio.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('prescription.builder') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2 px-4 rounded-lg border transition flex items-center gap-1.5">
                    <span>℞</span> Open Doctor Studio
                </a>
                <a href="{{ route('prescription.list') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2 px-4 rounded-lg border transition">
                    All Prescriptions
                </a>
            </div>
        </div>

        <!-- Chamber & Date Selection Strip -->
        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Select Hospital / Chamber</label>
                    <select wire:change="selectChamber($event.target.value)" class="w-full text-xs font-bold border rounded-md p-2 bg-gray-50 focus:bg-white text-gray-800">
                        @foreach($chambers as $c)
                            <option value="{{ $c->id }}" @selected($selected_chamber_id == $c->id)>
                                {{ $c->name }} ({{ $c->visiting_hours ?: 'Regular' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Queue Date</label>
                    <input type="date" wire:model.live="selected_date" class="w-full text-xs border rounded-md p-2 bg-gray-50 focus:bg-white">
                </div>
                <div class="md:col-span-4">
                    <div class="flex items-center justify-between bg-slate-50 border rounded-lg p-2.5 text-xs">
                        <div class="text-center">
                            <span class="block text-gray-400 text-[10px] uppercase font-bold">Total</span>
                            <span class="font-black text-gray-800 text-sm">{{ $stats['total'] }}</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-amber-600 text-[10px] uppercase font-bold">Waiting</span>
                            <span class="font-black text-amber-600 text-sm">{{ $stats['waiting'] }}</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-cyan-600 text-[10px] uppercase font-bold">With Doc</span>
                            <span class="font-black text-cyan-600 text-sm">{{ $stats['with_doctor'] }}</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-emerald-600 text-[10px] uppercase font-bold">Done</span>
                            <span class="font-black text-emerald-600 text-sm">{{ $stats['completed'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($activeChamber)
                <div class="mt-3 pt-3 border-t text-xs text-gray-500 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <strong>Doctor:</strong> {{ $activeChamber->doctor_name }} ({{ $activeChamber->specialty }}) |
                        <strong>Location:</strong> {{ $activeChamber->address }} ({{ $activeChamber->room_number }})
                    </div>
                    <div class="flex items-center gap-3">
                        @if($activeChamber->whatsapp_number)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $activeChamber->whatsapp_number) }}" target="_blank" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                                💬 WhatsApp Hotline: {{ $activeChamber->whatsapp_number }}
                            </a>
                        @endif
                        <span><strong>Serial Phone:</strong> {{ $activeChamber->phone }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Grid: Booking Form (Left) & Live Queue (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Quick Serial / Appointment Form -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border p-4">
                    <div class="flex items-center justify-between border-b pb-2 mb-3">
                        <h2 class="font-bold text-gray-800 text-xs uppercase flex items-center gap-1.5">
                            <span>📝</span> Book Patient Appointment
                        </h2>
                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded text-gray-500">Auto-Generates Serial</span>
                    </div>

                    @if($successMessage)
                        <div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800 flex items-center justify-between">
                            <div>
                                <strong>✓ {{ $successMessage }}</strong>
                                <div class="text-[11px] text-emerald-600">Patient is now added to today's doctor queue.</div>
                            </div>
                            <span class="text-xl font-black bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center">
                                {{ $generatedSerial }}
                            </span>
                        </div>
                    @endif

                    <form wire:submit.prevent="bookAppointment" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase">Patient Name *</label>
                            <input type="text" wire:model="patient_name" class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white" placeholder="e.g. Abdur Rahim">
                            @error('patient_name') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase">Age (Years) *</label>
                                <input type="number" wire:model="patient_age" class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white" placeholder="42">
                                @error('patient_age') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase">Gender *</label>
                                <select wire:model="patient_gender" class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase">Phone Number</label>
                            <input type="text" wire:model="patient_phone" class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white" placeholder="017xxxxxxxx">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase">Preferred Time Slot</label>
                            <input list="time-slots" wire:model="appointment_time" class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white" placeholder="e.g. 05:00 PM">
                            <datalist id="time-slots">
                                <option value="04:00 PM">04:00 PM</option>
                                <option value="04:30 PM">04:30 PM</option>
                                <option value="05:00 PM">05:00 PM</option>
                                <option value="05:30 PM">05:30 PM</option>
                                <option value="06:00 PM">06:00 PM</option>
                                <option value="06:30 PM">06:30 PM</option>
                                <option value="07:00 PM">07:00 PM</option>
                                <option value="07:30 PM">07:30 PM</option>
                                <option value="08:00 PM">08:00 PM</option>
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase">Chief Complaint / Reason</label>
                            <textarea wire:model="notes" rows="2" class="w-full border rounded-md p-2 text-xs bg-gray-50 focus:bg-white" placeholder="e.g. Kidney pain, blood pressure checkup"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-[#1e3fae] hover:bg-[#173087] text-white font-bold py-2.5 px-4 rounded-lg text-xs shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>+</span> Issue Serial Token & Add to Queue
                        </button>
                    </form>
                </div>

                <!-- "No Receptionist" Automated Public Link Card -->
                @php
                    $tenantSlug = auth()->user()?->tenant?->slug;
                    $publicBookingUrl = $tenantSlug ? route('appointment.public_book', $tenantSlug) : url('/');
                @endphp
                <div class="bg-gradient-to-br from-blue-50 to-orange-50 rounded-xl border border-blue-200 p-4 text-xs text-gray-700">
                    <div class="flex items-center gap-1.5 font-bold text-[#1e3fae] mb-1">
                        <span>💡</span> Patient Self-Booking Portal Link
                    </div>
                    <p class="text-[11px] text-gray-600 mb-3">
                        Share this public link with patients or print the door QR poster so patients can self-book serial tokens on their mobile phone:
                    </p>
                    <div class="bg-white border rounded-lg p-2 text-[11px] font-mono text-[#1e3fae] break-all select-all flex items-center justify-between mb-3">
                        <span class="truncate mr-2">{{ $publicBookingUrl }}</span>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $publicBookingUrl }}'); alert('Link copied to clipboard!');" class="text-[10px] bg-blue-100 hover:bg-blue-200 text-[#1e3fae] px-2 py-1 rounded font-bold whitespace-nowrap cursor-pointer">
                            Copy Link
                        </button>
                    </div>

                    <a href="{{ route('chamber.qr_standee', $selected_chamber_id ?? 1) }}" target="_blank" class="w-full bg-[#1e3fae] hover:bg-[#173087] text-white font-bold py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition text-center shadow-xs">
                        <span>🖨️</span> Print Chamber Door QR Poster (A4)
                    </a>
                </div>
            </div>

            <!-- Right: Live Queue Table -->
            <div class="lg:col-span-8 bg-white rounded-xl shadow-sm border p-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-3 mb-4">
                    <div>
                        <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                            <span>📋</span> Today's Waiting Queue: {{ \Carbon\Carbon::parse($selected_date)->format('d M, Y') }}
                        </h2>
                        <span class="text-xs text-gray-400">Total {{ count($appointments) }} patients registered</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-gray-500">Live sync with Doctor's Studio</span>
                    </div>
                </div>

                <!-- Booking Channel Filter Pills -->
                <div class="flex items-center gap-1.5 mb-4 flex-wrap">
                    <button
                        type="button"
                        wire:click="$set('filter_source', 'all')"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer @if($filter_source === 'all') bg-[#1e3fae] text-white @else bg-slate-100 hover:bg-slate-200 text-slate-700 @endif"
                    >
                        All Bookings ({{ $sourceCounts['all'] ?? 0 }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filter_source', 'whatsapp')"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1 @if($filter_source === 'whatsapp') bg-emerald-600 text-white @else bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 @endif"
                    >
                        <span>💬 WhatsApp</span> ({{ $sourceCounts['whatsapp'] ?? 0 }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filter_source', 'qr')"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1 @if($filter_source === 'qr') bg-blue-600 text-white @else bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 @endif"
                    >
                        <span>📱 QR Standee</span> ({{ $sourceCounts['qr'] ?? 0 }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filter_source', 'receptionist')"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1 @if($filter_source === 'receptionist') bg-slate-800 text-white @else bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 @endif"
                    >
                        <span>🏢 Receptionist</span> ({{ $sourceCounts['receptionist'] ?? 0 }})
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-slate-50 text-[11px] font-bold text-gray-500 uppercase border-b">
                            <tr>
                                <th class="p-2.5 text-center">Serial #</th>
                                <th class="p-2.5">Patient Details</th>
                                <th class="p-2.5">Time</th>
                                <th class="p-2.5">Complaints / Notes</th>
                                <th class="p-2.5 text-center">Status</th>
                                <th class="p-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($appointments as $app)
                                <tr class="hover:bg-slate-50 transition @if($app->status === 'with_doctor') bg-cyan-50/50 @endif">
                                    <td class="p-2.5 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full font-black text-xs @if($app->status === 'completed') bg-emerald-100 text-emerald-800 @elseif($app->status === 'with_doctor') bg-cyan-600 text-white @else bg-gray-100 text-gray-800 @endif">
                                            {{ $app->serial_no }}
                                        </span>
                                    </td>
                                    <td class="p-2.5">
                                        <div class="font-bold text-gray-800">{{ $app->patient_name }}</div>
                                        <div class="text-[11px] text-gray-400">
                                            {{ $app->patient_age }}Y, {{ $app->patient_gender }}
                                            @if($app->patient_phone)
                                                | <a href="tel:{{ $app->patient_phone }}" class="text-cyan-700 hover:underline">{{ $app->patient_phone }}</a>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            @if($app->booking_source === 'whatsapp')
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[9px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    💬 WhatsApp
                                                </span>
                                            @elseif($app->booking_source === 'qr')
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[9px] font-black bg-blue-100 text-blue-800 border border-blue-200">
                                                    📱 QR
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[9px] font-black bg-slate-100 text-slate-700 border border-slate-200">
                                                    🏢 Desk
                                                </span>
                                            @endif

                                            @if($app->booking_reference)
                                                <span class="text-[9px] font-mono text-slate-500 font-bold bg-slate-50 px-1 py-0.2 rounded border border-slate-100">
                                                    {{ $app->booking_reference }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-2.5 text-gray-700 font-medium">
                                        {{ $app->appointment_time ?: 'Anytime' }}
                                    </td>
                                    <td class="p-2.5 text-[11px] text-gray-500 max-w-xs truncate">
                                        {{ $app->notes ?: '—' }}
                                    </td>
                                    <td class="p-2.5 text-center">
                                        @if($app->status === 'waiting')
                                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                Waiting
                                            </span>
                                        @elseif($app->status === 'with_doctor')
                                            <span class="bg-cyan-100 text-cyan-800 text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                                                With Doctor
                                            </span>
                                        @elseif($app->status === 'completed')
                                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                Completed ✓
                                            </span>
                                        @elseif($app->status === 'cancelled')
                                            <span class="bg-red-100 text-red-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-right space-x-1 whitespace-nowrap">
                                        @if($app->patient_phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $app->patient_phone) }}?text={{ urlencode('Hello ' . $app->patient_name . ', your appointment Serial #' . $app->serial_no . ' with ' . ($activeChamber->doctor_name ?? 'Doctor') . ' is confirmed for today at ' . ($app->appointment_time ?? 'the chamber') . '.') }}" target="_blank" class="inline-block bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-1 rounded text-[11px] font-bold" title="Send WhatsApp Confirmation">
                                                💬 WhatsApp
                                            </a>
                                        @endif

                                        @if($app->status === 'waiting')
                                            <button type="button" wire:click="updateStatus({{ $app->id }}, 'with_doctor')" class="bg-cyan-50 hover:bg-cyan-100 text-cyan-700 px-2 py-1 rounded text-[11px] font-bold border border-cyan-200 cursor-pointer">
                                                Call In
                                            </button>
                                        @elseif($app->status === 'with_doctor')
                                            <button type="button" wire:click="updateStatus({{ $app->id }}, 'completed')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-[11px] font-bold border border-emerald-200 cursor-pointer">
                                                Mark Done
                                            </button>
                                        @endif

                                        <button type="button" wire:click="deleteAppointment({{ $app->id }})" class="text-red-400 hover:text-red-600 px-1 font-bold cursor-pointer" title="Remove">
                                            ×
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-400">
                                        No patients registered in the queue for this date.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
