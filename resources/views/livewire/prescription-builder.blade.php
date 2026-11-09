<div class="max-w-7xl mx-auto py-6 px-4" x-data="{
    listeningField: null,
    speechLang: 'en-US',
    startVoice(fieldName) {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            alert('Voice dictation is supported in Google Chrome, Microsoft Edge, and Safari browsers.');
            return;
        }
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.lang = this.speechLang;
        recognition.continuous = false;
        recognition.interimResults = false;

        this.listeningField = fieldName;

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            if (transcript) {
                @this.appendQuickText(fieldName, transcript);
            }
            this.listeningField = null;
        };

        recognition.onerror = () => {
            this.listeningField = null;
        };

        recognition.onend = () => {
            this.listeningField = null;
        };

        recognition.start();
    }
}">
    <!-- Top Header: Doctor Specialty & Hospital Chamber Banner (Bikrify Customized) -->
    <div class="bg-gradient-to-r from-cyan-900 via-cyan-800 to-slate-900 text-white rounded-2xl shadow-lg p-5 mb-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Doctor & Specialty Profile -->
            <div class="space-y-1.5 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="bg-amber-400 text-cyan-950 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-xs">
                        🎯 {{ $specialty }}
                    </span>
                    <span class="text-xs bg-cyan-700/60 px-2.5 py-0.5 rounded-md border border-cyan-500/30 font-medium">
                        {{ $hospital_affiliation }}
                    </span>
                </div>

                <div class="pt-1">
                    <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                        {{ $doctor_name }}
                    </h1>
                    <p class="text-xs text-cyan-200 font-medium">{{ $doctor_qualification }}</p>
                    <p class="text-xs text-cyan-300 font-semibold">{{ $doctor_designation }}</p>
                </div>

                <!-- Hospital & Seating Info -->
                <div class="pt-2 text-xs text-cyan-100 flex flex-wrap items-center gap-x-4 gap-y-1.5 border-t border-cyan-700/50">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-amber-300">🏥 Hospital / Chamber:</span>
                        <span class="font-bold text-white">{{ $chamber_name }}</span>
                        @if($room_number) <span class="text-cyan-300">({{ $room_number }})</span> @endif
                    </div>
                    <div>
                        <span class="text-cyan-200">📍 {{ $chamber_address }}</span>
                    </div>
                    <div>
                        <span class="text-cyan-200">⏰ Visiting: <strong class="text-white">{{ $chamber_visiting_hours }}</strong></span>
                    </div>
                </div>

                <!-- Contact & WhatsApp Hotline -->
                <div class="text-xs text-cyan-100 flex flex-wrap items-center gap-3 pt-1">
                    <span><strong>📞 Serial Hotline:</strong> {{ $chamber_phone }}</span>
                    @if($secondary_phone)
                        <span><strong>Direct:</strong> {{ $secondary_phone }}</span>
                    @endif
                    @if($whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp_number) }}" target="_blank" class="inline-flex items-center gap-1 bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-0.5 rounded-full font-bold transition">
                            <span>💬 WhatsApp Serial:</span> {{ $whatsapp_number }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Top Right Action Controls -->
            <div class="flex flex-col sm:flex-row lg:flex-col items-end gap-2.5">
                <div class="flex items-center gap-2">
                    <select wire:change="selectChamber($event.target.value)" class="text-xs font-bold text-gray-800 border rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-amber-400">
                        @foreach($allChambers as $c)
                            <option value="{{ $c->id }}" @selected($selected_chamber_id == $c->id)>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="button" wire:click="openNewChamberModal" class="text-xs bg-cyan-700 hover:bg-cyan-600 text-white px-3 py-2 rounded-lg font-bold border border-cyan-500/50 transition">
                        + Hospital
                    </button>
                    @if($selected_chamber_id)
                        <button type="button" wire:click="openEditChamberModal({{ $selected_chamber_id }})" class="text-xs bg-amber-400 hover:bg-amber-500 text-cyan-950 px-3 py-2 rounded-lg font-bold transition cursor-pointer">
                            ⚙️ Setup
                        </button>
                        <a href="{{ route('chamber.qr_standee', $selected_chamber_id) }}" target="_blank" class="text-xs bg-slate-800 hover:bg-slate-700 text-cyan-200 px-3 py-2 rounded-lg font-bold transition flex items-center gap-1" title="Print Chamber Door QR Standee">
                            <span>📲</span> Door QR
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('appointment.manager') }}" class="bg-cyan-700/80 hover:bg-cyan-700 text-cyan-100 text-xs font-bold px-3 py-2.5 rounded-lg border border-cyan-500/40 transition flex items-center gap-1.5">
                        <span>👥</span> Queue Desk
                    </a>
                    <button wire:click="saveAndPrint" class="bg-emerald-500 hover:bg-emerald-600 text-white font-black py-2.5 px-6 rounded-lg shadow-md transition flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Save & Print A4 Pad
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Patient Queue (Receptionist / Self-Booking Sync) -->
    @if($todayAppointments->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-5 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="bg-amber-500 text-white text-xs font-black px-2 py-0.5 rounded">LIVE QUEUE</span>
                <span class="text-xs font-bold text-amber-900">Today's Waiting Patients ({{ $todayAppointments->count() }}):</span>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto py-1">
                @foreach($todayAppointments as $qApp)
                    <button type="button"
                            wire:click="callQueuePatient({{ $qApp->id }})"
                            class="text-xs px-3 py-1.5 rounded-lg font-bold border transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer @if($current_appointment_id == $qApp->id) bg-cyan-700 text-white border-cyan-800 shadow-xs @else bg-white hover:bg-amber-100 text-amber-900 border-amber-300 @endif">
                        <span class="w-5 h-5 rounded-full bg-amber-400 text-cyan-950 font-black text-[11px] flex items-center justify-center">#{{ $qApp->serial_no }}</span>
                        <span>{{ $qApp->patient_name }} ({{ $qApp->patient_age }}Y)</span>
                        <span class="text-[10px] text-amber-700">@if($current_appointment_id == $qApp->id) (Active) @else (Call) @endif</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Patient Header & Lookup Bar -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-gray-200 mb-3">
            <!-- Patient Search (Existing Patient Lookup) -->
            <div class="relative flex-1 max-w-md">
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                    🔍 Quick Search Old Patient (Phone / Name / Patient ID)
                </label>
                <input type="text"
                       wire:model.live.debounce.250ms="patient_search"
                       class="w-full border rounded-md px-3 py-1.5 text-xs bg-gray-50 focus:bg-white focus:ring-1 focus:ring-cyan-500"
                       placeholder="Search 017... or patient name to auto-load history">

                @if(!empty($patientSearchResults))
                    <div class="absolute z-50 bg-white border rounded-md shadow-xl w-full mt-1 max-h-56 overflow-y-auto">
                        <div class="p-1.5 text-[11px] font-bold text-gray-400 bg-gray-50 border-b">Select Existing Patient:</div>
                        @foreach($patientSearchResults as $p)
                            <button type="button"
                                    wire:click="selectPatient({{ $p['id'] }})"
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-cyan-50 border-b flex items-center justify-between cursor-pointer">
                                <div>
                                    <span class="font-bold text-cyan-800">{{ $p['name'] }}</span>
                                    <span class="text-gray-500 ml-1">({{ $p['age'] }}Y, {{ $p['gender'] }})</span>
                                    <span class="block text-[11px] text-gray-400">{{ $p['phone'] ?: 'No phone' }} | {{ $p['patient_id'] }}</span>
                                </div>
                                <span class="text-[11px] bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full font-semibold">Load History</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Patient Status Indicator -->
            <div class="flex items-center gap-2">
                @if($is_existing_patient)
                    <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-bold flex items-center gap-1">
                        ✓ Old Patient Record
                    </span>
                    @if(!empty($patientHistory))
                        <button type="button"
                                wire:click="$toggle('showHistoryModal')"
                                class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-md font-semibold transition cursor-pointer">
                            📋 Past Visits ({{ count($patientHistory) }})
                        </button>
                    @endif
                    <button type="button"
                            wire:click="resetPatient"
                            class="text-xs text-red-600 hover:text-red-800 font-semibold underline">
                        Clear / New Patient
                    </button>
                @else
                    <span class="bg-gray-100 text-gray-600 text-xs px-2.5 py-1 rounded-full font-semibold">
                        New Patient
                    </span>
                @endif
            </div>
        </div>

        <!-- Patient Detail Fields -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase">Patient Name *</label>
                <input type="text" wire:model="patient_name" class="mt-1 w-full border rounded-md p-1.5 text-xs bg-white" placeholder="e.g. Rahim Ahmed">
                @error('patient_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase">Age (Years) *</label>
                <input type="number" wire:model="patient_age" class="mt-1 w-full border rounded-md p-1.5 text-xs bg-white" placeholder="e.g. 45">
                @error('patient_age') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase">Gender *</label>
                <select wire:model="patient_gender" class="mt-1 w-full border rounded-md p-1.5 text-xs bg-white">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase">Phone Number</label>
                <input type="text" wire:model="patient_phone" class="mt-1 w-full border rounded-md p-1.5 text-xs bg-white" placeholder="017xxxxxxxx">
            </div>
        </div>
    </div>

    <!-- Less Input Bar: 1-Click Clinical Full Bundles & Quick Drug Kits -->
    <div class="bg-gradient-to-r from-cyan-50 via-slate-50 to-amber-50 border border-cyan-200 rounded-xl p-3 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-black uppercase text-cyan-900 flex items-center gap-1">
                    <span>⚡</span> 1-Click Less-Input Automation:
                </span>
                @if(isset($specialtyTemplates[$specialty]['bundle']))
                    <button type="button"
                            wire:click="applySpecialtyBundle"
                            class="bg-cyan-700 hover:bg-cyan-800 text-white text-xs px-3 py-1 rounded-md font-bold transition shadow-xs cursor-pointer flex items-center gap-1">
                        {{ $specialtyTemplates[$specialty]['bundle']['name'] }}
                    </button>
                @endif
            </div>

            <!-- 1-Click Drug Kits -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-gray-600">Quick Drug Kits:</span>
                @foreach($specialtyTemplates[$specialty]['kits'] ?? [] as $kIdx => $sKit)
                    <button type="button"
                            wire:click="applyDrugKit({{ $kIdx }})"
                            class="bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 text-xs px-2.5 py-1 rounded-md font-bold transition shadow-xs cursor-pointer">
                        {{ $sKit['name'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Dual Column Split Layout -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Left Sidebar: Clinical Notes & Specialty Fast Chips -->
        <div class="md:col-span-4 space-y-4">
            <!-- Specialty Quick Selector & Voice Language Switcher -->
            <div class="bg-white rounded-xl shadow-sm border p-3">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-bold text-gray-700 uppercase flex items-center gap-1">
                        <span>🎯 Active Doctor Specialty</span>
                    </label>
                    <!-- Voice Language Toggle -->
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] text-gray-400">🎙️ Voice:</span>
                        <select x-model="speechLang" class="text-[10px] border rounded px-1 py-0.5 bg-gray-50 text-gray-700">
                            <option value="en-US">English</option>
                            <option value="bn-BD">বাংলা (Bengali)</option>
                        </select>
                    </div>
                </div>
                <select wire:model.live="specialty" class="w-full border rounded-md p-1.5 text-xs font-bold text-cyan-800 bg-cyan-50">
                    @foreach(array_keys($specialtyTemplates) as $spec)
                        <option value="{{ $spec }}">{{ $spec }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Fast Vitals Calculator Bar (BP & BMI) -->
            <div class="bg-white rounded-xl shadow-sm border p-3">
                <div class="flex items-center justify-between mb-2 border-b pb-1">
                    <span class="text-xs font-bold text-gray-700 uppercase flex items-center gap-1">
                        <span>🩺</span> Patient Vitals (Auto-BMI & BP)
                    </span>
                    <button type="button" wire:click="insertVitals" class="text-[11px] bg-cyan-100 hover:bg-cyan-200 text-cyan-800 px-2 py-0.5 rounded font-bold transition">
                        + Insert into O/E
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase">BP Sys/Dia</label>
                        <div class="flex items-center gap-1">
                            <input type="number" wire:model="vital_bp_systolic" class="w-full border rounded p-1 text-xs text-center" placeholder="140">
                            <span>/</span>
                            <input type="number" wire:model="vital_bp_diastolic" class="w-full border rounded p-1 text-xs text-center" placeholder="90">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase">Pulse (/min)</label>
                        <input type="number" wire:model="vital_pulse" class="w-full border rounded p-1 text-xs text-center" placeholder="80">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase">RBS (mmol/L)</label>
                        <input type="number" step="0.1" wire:model="vital_rbs" class="w-full border rounded p-1 text-xs text-center" placeholder="7.8">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase">Weight (kg)</label>
                        <input type="number" step="0.5" wire:model="vital_weight" class="w-full border rounded p-1 text-xs text-center" placeholder="68">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase">Height (cm)</label>
                        <input type="number" wire:model="vital_height_cm" class="w-full border rounded p-1 text-xs text-center" placeholder="165">
                    </div>
                    <div class="flex items-end">
                        @if($vital_weight && $vital_height_cm)
                            @php
                                $hM = ((float)$vital_height_cm)/100;
                                $bmiVal = $hM > 0 ? round(((float)$vital_weight)/($hM*$hM), 1) : 0;
                            @endphp
                            <div class="text-[11px] font-bold text-cyan-800 bg-cyan-50 p-1 rounded border w-full text-center">
                                BMI: {{ $bmiVal }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chief Complaints -->
            <div class="bg-white rounded-xl shadow-sm border p-3">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <h2 class="font-bold text-gray-700 text-xs uppercase">Chief Complaints</h2>
                        <!-- Voice Mic Button -->
                        <button type="button"
                                @click="startVoice('chief_complaints')"
                                :class="listeningField === 'chief_complaints' ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="text-[11px] px-1.5 py-0.5 rounded font-bold flex items-center gap-0.5"
                                title="Click to speak (Voice dictation)">
                            🎙️ <span x-show="listeningField === 'chief_complaints'">Listening...</span>
                        </button>
                    </div>
                    <button type="button" wire:click="clearField('chief_complaints')" class="text-[10px] text-gray-400 hover:text-red-500">Clear</button>
                </div>
                <!-- Quick Chips -->
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($specialtyTemplates[$specialty]['complaints'] ?? [] as $chip)
                        <button type="button"
                                wire:click="appendQuickText('chief_complaints', '{{ addslashes($chip) }}')"
                                class="text-[11px] bg-slate-100 hover:bg-cyan-100 text-slate-700 hover:text-cyan-800 px-2 py-0.5 rounded-full border transition cursor-pointer">
                            + {{ $chip }}
                        </button>
                    @endforeach
                </div>
                <textarea wire:model="chief_complaints" rows="3" class="w-full border rounded-md p-2 text-xs" placeholder="e.g. Swelling of body x 1 month, High BP (or click mic 🎙️ to dictate)"></textarea>
            </div>

            @unless(auth()->user()?->isAssistant())
            <!-- On Examination (O/E) -->
            <div class="bg-white rounded-xl shadow-sm border p-3">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <h2 class="font-bold text-gray-700 text-xs uppercase">On Examination (O/E)</h2>
                        <button type="button"
                                @click="startVoice('clinical_findings')"
                                :class="listeningField === 'clinical_findings' ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="text-[11px] px-1.5 py-0.5 rounded font-bold flex items-center gap-0.5">
                            🎙️ <span x-show="listeningField === 'clinical_findings'">Listening...</span>
                        </button>
                    </div>
                    <button type="button" wire:click="clearField('clinical_findings')" class="text-[10px] text-gray-400 hover:text-red-500">Clear</button>
                </div>
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($specialtyTemplates[$specialty]['findings'] ?? [] as $chip)
                        <button type="button"
                                wire:click="appendQuickText('clinical_findings', '{{ addslashes($chip) }}')"
                                class="text-[11px] bg-slate-100 hover:bg-cyan-100 text-slate-700 hover:text-cyan-800 px-2 py-0.5 rounded-full border transition cursor-pointer">
                            + {{ $chip }}
                        </button>
                    @endforeach
                </div>
                <textarea wire:model="clinical_findings" rows="2" class="w-full border rounded-md p-2 text-xs" placeholder="BP: 150/90, Edema ++, Anemia +"></textarea>
            </div>

            <!-- Diagnosis -->
            <div class="bg-white rounded-xl shadow-sm border p-3">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <h2 class="font-bold text-gray-700 text-xs uppercase">Diagnosis</h2>
                        <button type="button"
                                @click="startVoice('diagnosis')"
                                :class="listeningField === 'diagnosis' ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="text-[11px] px-1.5 py-0.5 rounded font-bold flex items-center gap-0.5">
                            🎙️
                        </button>
                    </div>
                    <button type="button" wire:click="clearField('diagnosis')" class="text-[10px] text-gray-400 hover:text-red-500">Clear</button>
                </div>
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($specialtyTemplates[$specialty]['diagnosis'] ?? [] as $chip)
                        <button type="button"
                                wire:click="appendQuickText('diagnosis', '{{ addslashes($chip) }}')"
                                class="text-[11px] bg-amber-50 hover:bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full border border-amber-200 transition cursor-pointer">
                            + {{ $chip }}
                        </button>
                    @endforeach
                </div>
                <textarea wire:model="diagnosis" rows="2" class="w-full border rounded-md p-2 text-xs font-semibold" placeholder="e.g. Chronic Kidney Disease (Stage 4)"></textarea>
            </div>
            @endunless

            <!-- Investigations -->
            <div class="bg-white rounded-xl shadow-sm border p-3">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <h2 class="font-bold text-gray-700 text-xs uppercase">Investigations</h2>
                        <button type="button"
                                @click="startVoice('investigations')"
                                :class="listeningField === 'investigations' ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="text-[11px] px-1.5 py-0.5 rounded font-bold flex items-center gap-0.5">
                            🎙️
                        </button>
                    </div>
                    <button type="button" wire:click="clearField('investigations')" class="text-[10px] text-gray-400 hover:text-red-500">Clear</button>
                </div>
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($specialtyTemplates[$specialty]['investigations'] ?? [] as $chip)
                        <button type="button"
                                wire:click="appendQuickText('investigations', '{{ addslashes($chip) }}')"
                                class="text-[11px] bg-slate-100 hover:bg-cyan-100 text-slate-700 hover:text-cyan-800 px-2 py-0.5 rounded-full border transition cursor-pointer">
                            + {{ $chip }}
                        </button>
                    @endforeach
                </div>
                <textarea wire:model="investigations" rows="2" class="w-full border rounded-md p-2 text-xs" placeholder="S. Creatinine, Electrolytes, Urine R/M/E"></textarea>
            </div>
        </div>

        <!-- Right Side: Rx & Fast Medication Table -->
        <div class="md:col-span-8 bg-white rounded-xl shadow-sm border p-5">
            <!-- Medicine Search Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-3xl font-serif font-black text-cyan-900">℞</span>
                    <span class="text-xs font-bold text-gray-500 uppercase">Prescribed Medicines</span>
                </div>

                <div class="relative w-full sm:w-96">
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.200ms="search"
                               placeholder="Search 42,000+ medicines (e.g. Napa, Napa Extra, Ketosteril)..."
                               class="w-full border rounded-md pl-3 pr-8 py-1.5 text-xs bg-gray-50 focus:bg-white focus:ring-1 focus:ring-cyan-500">
                        @if($search)
                            <button type="button" wire:click="$set('search', '')" class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600 font-bold text-xs">✕</button>
                        @endif
                    </div>

                    @if(!empty($searchResults))
                        <div class="absolute z-50 bg-white border rounded-md shadow-2xl w-full mt-1 max-h-72 overflow-y-auto">
                            <div class="p-1.5 text-[11px] font-bold text-gray-400 bg-gray-50 border-b flex justify-between">
                                <span>Found {{ count($searchResults) }} medicines:</span>
                                <span>Click to add</span>
                            </div>
                            @foreach($searchResults as $res)
                                <button type="button"
                                        wire:click="addItem({{ json_encode(['type' => $res['dosage_form'] ?? 'Tab.', 'name' => $res['brand_name'], 'generic' => $res['generic_name'], 'strength' => $res['strength'], 'dosage' => '1+0+1', 'duration' => '7 days', 'instruction' => 'After meal (খাবার পরে)']) }})"
                                        class="w-full text-left px-3 py-2 text-xs hover:bg-cyan-50 border-b flex flex-col justify-between cursor-pointer">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-800">
                                            <span class="text-cyan-700">{{ $res['dosage_form'] }}</span> {{ $res['brand_name'] }}
                                            @if($res['strength']) <span class="text-gray-600 font-normal">({{ $res['strength'] }})</span> @endif
                                        </span>
                                        <span class="text-[10px] text-gray-400 truncate max-w-[120px]">{{ $res['manufacturer'] }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 italic mt-0.5">
                                        Generic: {{ $res['generic_name'] }}
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @error('items') <div class="text-xs text-red-500 mb-2 font-semibold">{{ $message }}</div> @enderror
            @error('items.*.name') <div class="text-xs text-red-500 mb-2 font-semibold">{{ $message }}</div> @enderror

            <!-- Medicine Table Headers -->
            <div class="hidden sm:grid grid-cols-12 gap-2 text-[11px] font-bold text-gray-500 uppercase px-3 pb-1 border-b">
                <div class="col-span-2">Form</div>
                <div class="col-span-4">Brand / Medicine Name (Manual / Search)</div>
                <div class="col-span-2">Time (1+0+1)</div>
                <div class="col-span-2">Duration</div>
                <div class="col-span-2">When to take</div>
            </div>

            <!-- Medicine Item Rows with Quick Dropdowns -->
            <div class="space-y-3 mt-2">
                @foreach($items as $i => $item)
                    <div class="p-2.5 border rounded-lg bg-gray-50 grid grid-cols-1 sm:grid-cols-12 gap-2 items-center" wire:key="med-item-{{ $i }}">
                        <!-- Form Dropdown -->
                        <div class="sm:col-span-2">
                            <label class="block sm:hidden text-[10px] font-bold text-gray-500 uppercase">Form</label>
                            <input list="forms-list" wire:model="items.{{ $i }}.type" class="w-full text-xs border rounded p-1.5 bg-white font-medium" placeholder="Form">
                        </div>

                        <!-- Brand / Medicine Name -->
                        <div class="sm:col-span-4">
                            <label class="block sm:hidden text-[10px] font-bold text-gray-500 uppercase">Medicine Name</label>
                            <input type="text"
                                   wire:model="items.{{ $i }}.name"
                                   class="w-full text-xs font-bold border rounded p-1.5 bg-white text-gray-900 placeholder:text-gray-400"
                                   placeholder="Type any medicine name...">
                            @if(!empty($item['generic']))
                                <div class="text-[10px] text-gray-500 truncate mt-0.5">{{ $item['generic'] }} ({{ $item['strength'] }})</div>
                            @endif
                        </div>

                        <!-- Timing Dropdown (1+0+0, 1+0+1, etc.) -->
                        <div class="sm:col-span-2">
                            <label class="block sm:hidden text-[10px] font-bold text-gray-500 uppercase">Timing</label>
                            <input list="timing-list" wire:model="items.{{ $i }}.dosage" class="w-full text-xs border rounded p-1.5 bg-white font-semibold text-center text-cyan-800" placeholder="1+0+1">
                        </div>

                        <!-- Duration Dropdown -->
                        <div class="sm:col-span-2">
                            <label class="block sm:hidden text-[10px] font-bold text-gray-500 uppercase">Duration</label>
                            <input list="duration-list" wire:model="items.{{ $i }}.duration" class="w-full text-xs border rounded p-1.5 bg-white text-center" placeholder="7 days">
                        </div>

                        <!-- When to take Instructions -->
                        <div class="sm:col-span-2 flex items-center gap-1">
                            <div class="w-full">
                                <label class="block sm:hidden text-[10px] font-bold text-gray-500 uppercase">Instruction</label>
                                <input list="instruction-list" wire:model="items.{{ $i }}.instruction" class="w-full text-xs border rounded p-1.5 bg-white" placeholder="After meal">
                            </div>
                            <button type="button"
                                    wire:click="removeItem({{ $i }})"
                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded p-1 font-bold text-base cursor-pointer self-end sm:self-center"
                                    title="Remove row">
                                ×
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Add Row Button -->
            <button type="button" wire:click="addItem" class="mt-4 text-xs font-bold text-cyan-700 hover:text-cyan-900 bg-cyan-50 hover:bg-cyan-100 border border-cyan-200 px-3 py-1.5 rounded-md flex items-center gap-1.5 transition cursor-pointer">
                <span>+</span> Add Another Medicine Row (Manual / Custom)
            </button>

            <!-- Special Advice & Follow-Up Generator -->
            <div class="mt-6 border-t pt-4">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <label class="block text-xs font-semibold text-gray-600 uppercase">Doctor's Special Advice</label>
                        <button type="button"
                                @click="startVoice('advice')"
                                :class="listeningField === 'advice' ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="text-[11px] px-1.5 py-0.5 rounded font-bold flex items-center gap-0.5">
                            🎙️
                        </button>
                    </div>
                    <button type="button" wire:click="clearField('advice')" class="text-[10px] text-gray-400 hover:text-red-500">Clear</button>
                </div>

                <!-- Fast Follow-up Shortcut Buttons -->
                <div class="flex items-center gap-1.5 mb-2 flex-wrap">
                    <span class="text-[11px] font-bold text-gray-500">Next Follow-up:</span>
                    <button type="button" wire:click="addFollowUp('7 days')" class="text-[11px] bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 px-2 py-0.5 rounded font-semibold">+ 7 Days</button>
                    <button type="button" wire:click="addFollowUp('15 days')" class="text-[11px] bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 px-2 py-0.5 rounded font-semibold">+ 15 Days</button>
                    <button type="button" wire:click="addFollowUp('1 month')" class="text-[11px] bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 px-2 py-0.5 rounded font-semibold">+ 1 Month</button>
                    <button type="button" wire:click="addFollowUp('3 months')" class="text-[11px] bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 px-2 py-0.5 rounded font-semibold">+ 3 Months</button>
                </div>

                <!-- Specialty Advice Chips -->
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($specialtyTemplates[$specialty]['advice'] ?? [] as $chip)
                        <button type="button"
                                wire:click="appendQuickText('advice', '{{ addslashes($chip) }}')"
                                class="text-[11px] bg-slate-100 hover:bg-cyan-100 text-slate-700 hover:text-cyan-800 px-2 py-0.5 rounded-full border transition cursor-pointer">
                            + {{ $chip }}
                        </button>
                    @endforeach
                </div>
                <textarea wire:model="advice" rows="3" class="w-full border rounded-md p-2 text-xs" placeholder="e.g. Low protein diet, fluid restriction, drink 1.5L daily, avoid NSAIDs."></textarea>
            </div>
        </div>
    </div>

    <!-- Datalists for Fast Selections -->
    <datalist id="forms-list">
        <option value="Tab.">Tablet</option>
        <option value="Cap.">Capsule</option>
        <option value="Inj.">Injection</option>
        <option value="Syr.">Syrup / Suspension</option>
        <option value="Drop">Eye / Ear / Paediatric Drop</option>
        <option value="Oint.">Ointment / Cream</option>
        <option value="Inhaler">Inhaler / Rotacap</option>
        <option value="Supp.">Suppository</option>
        <option value="Sachet">Sachet / Powder</option>
    </datalist>

    <datalist id="timing-list">
        <option value="1+0+0">1+0+0 (Morning only / সকালে)</option>
        <option value="0+1+0">0+1+0 (Noon only / দুপুরে)</option>
        <option value="0+0+1">0+0+1 (Night only / রাতে)</option>
        <option value="1+0+1">1+0+1 (Morning & Night / সকাল + রাত)</option>
        <option value="1+1+1">1+1+1 (Three times daily / ৩ বেলা)</option>
        <option value="1+1+1+1">1+1+1+1 (Four times daily / ৪ বেলা)</option>
        <option value="1/2+0+1/2">1/2+0+1/2 (Half tablet twice / অর্ধেক করে ২ বেলা)</option>
        <option value="1+0+0+1">1+0+0+1 (Morning & Bedtime)</option>
        <option value="1 spoon 3 times">1 চামচ করে দিনে ৩ বার</option>
        <option value="2 puffs twice daily">2 Puffs Twice Daily</option>
        <option value="As needed (PRN)">প্রয়োজনে (ব্যথা বা জ্বরে)</option>
    </datalist>

    <datalist id="duration-list">
        <option value="3 days">3 days (৩ দিন)</option>
        <option value="5 days">5 days (৫ দিন)</option>
        <option value="7 days">7 days (৭ দিন)</option>
        <option value="10 days">10 days (১০ দিন)</option>
        <option value="14 days">14 days (১৪ দিন)</option>
        <option value="1 month">1 month (১ মাস)</option>
        <option value="2 months">2 months (২ মাস)</option>
        <option value="3 months">3 months (৩ মাস)</option>
        <option value="6 months">6 months (৬ মাস)</option>
        <option value="Continue (চলবে)">Continue (চলবে)</option>
    </datalist>

    <datalist id="instruction-list">
        <option value="After meal (খাবার পরে)">After meal (খাবার পরে)</option>
        <option value="Before meal (খাবার আগে)">Before meal (খাবার আগে)</option>
        <option value="With meal (খাবারের সাথে)">With meal (খাবারের সাথে)</option>
        <option value="Empty stomach (খালি পেটে)">Empty stomach (খালি পেটে)</option>
        <option value="At bedtime (ঘুমানোর আগে)">At bedtime (ঘুমানোর আগে)</option>
        <option value="Before breakfast (সকালের নাস্তার আগে)">Before breakfast (সকালের নাস্তার আগে)</option>
        <option value="After breakfast (সকালের নাস্তার পরে)">After breakfast (সকালের নাস্তার পরে)</option>
        <option value="As directed">As directed</option>
    </datalist>

    <!-- Patient History Modal -->
    @if($showHistoryModal)
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[85vh] flex flex-col">
                <div class="p-4 border-b flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base text-gray-800">Past Prescriptions: {{ $patient_name }}</h3>
                        <p class="text-xs text-gray-500">Review patient history or repeat previous medications</p>
                    </div>
                    <button type="button" wire:click="$set('showHistoryModal', false)" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
                </div>
                <div class="p-4 overflow-y-auto space-y-4 flex-1">
                    @forelse($patientHistory as $past)
                        <div class="border rounded-lg p-3 bg-gray-50 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between border-b pb-2 mb-2">
                                <div>
                                    <span class="font-bold text-xs text-cyan-800">{{ $past['prescription_no'] }}</span>
                                    <span class="text-xs text-gray-500 ml-2">Date: {{ \Carbon\Carbon::parse($past['prescription_date'])->format('d M, Y') }}</span>
                                </div>
                                <button type="button"
                                        wire:click="repeatPreviousPrescription({{ $past['id'] }})"
                                        class="bg-cyan-600 hover:bg-cyan-700 text-white text-xs px-3 py-1 rounded font-bold transition cursor-pointer">
                                    ↻ Repeat Medicines from this Visit
                                </button>
                            </div>
                            @if(!empty($past['diagnosis']))
                                <div class="text-xs mb-1"><strong>Diagnosis:</strong> {{ $past['diagnosis'] }}</div>
                            @endif
                            @if(!empty($past['chief_complaints']))
                                <div class="text-xs text-gray-600 mb-2"><strong>Complaints:</strong> {{ $past['chief_complaints'] }}</div>
                            @endif
                            @if(!empty($past['items']))
                                <div class="text-xs font-semibold text-gray-700 mb-1">Medicines Given:</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                    @foreach($past['items'] as $pItem)
                                        <div class="text-[11px] bg-white border rounded px-2 py-1 flex items-center justify-between">
                                            <span><strong>{{ $pItem['type'] }} {{ $pItem['medicine_name'] }}</strong></span>
                                            <span class="text-gray-500">{{ $pItem['dosage'] }} ({{ $pItem['duration'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 text-sm">No past prescriptions found for this patient.</div>
                    @endforelse
                </div>
                <div class="p-3 border-t bg-gray-50 text-right">
                    <button type="button" wire:click="$set('showHistoryModal', false)" class="text-xs bg-gray-200 hover:bg-gray-300 px-4 py-1.5 rounded font-bold">Close</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Chamber Setup Modal (Bikrify Onboarding / Setup) -->
    @if($showChamberModal)
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-base text-cyan-900">
                            {{ $chamber_form_id ? 'Customize Hospital / Chamber' : 'Add New Hospital / Chamber' }}
                        </h3>
                        <p class="text-[11px] text-gray-500">Bikrify Doctor & Chamber Onboarding Profile</p>
                    </div>
                    <button type="button" wire:click="closeChamberModal" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-semibold text-gray-700">Hospital / Chamber Name *</label>
                        <input type="text" wire:model="chamber_form_name" class="w-full border rounded p-2 text-xs" placeholder="e.g. Popular Diagnostic Center - Dhanmondi">
                        @error('chamber_form_name') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-gray-700">Hospital Affiliation / Group</label>
                            <input type="text" wire:model="chamber_form_hospital_affiliation" class="w-full border rounded p-2 text-xs" placeholder="e.g. Square Hospitals Ltd.">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700">Room / Chamber / Seat No.</label>
                            <input type="text" wire:model="chamber_form_room_number" class="w-full border rounded p-2 text-xs" placeholder="Room #405, 4th Floor">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Doctor Name *</label>
                        <input type="text" wire:model="chamber_form_doctor_name" class="w-full border rounded p-2 text-xs" placeholder="e.g. Prof. Dr. Mahbubur Rahman">
                        @error('chamber_form_doctor_name') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Doctor Qualifications</label>
                        <input type="text" wire:model="chamber_form_doctor_qualification" class="w-full border rounded p-2 text-xs" placeholder="MBBS, FCPS, MD (Nephrology)">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Doctor Designation / Department</label>
                        <input type="text" wire:model="chamber_form_doctor_designation" class="w-full border rounded p-2 text-xs" placeholder="Professor & Head of Department">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Specialty *</label>
                        <input type="text" wire:model="chamber_form_specialty" class="w-full border rounded p-2 text-xs" placeholder="Nephrology / CKD Specialist">
                        @error('chamber_form_specialty') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Address / Location</label>
                        <input type="text" wire:model="chamber_form_address" class="w-full border rounded p-2 text-xs" placeholder="House 16, Road 2, Dhanmondi, Dhaka">
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block font-semibold text-gray-700">Serial Phone</label>
                            <input type="text" wire:model="chamber_form_phone" class="w-full border rounded p-2 text-xs" placeholder="+880 1819-xxxxxx">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700">Secondary Phone</label>
                            <input type="text" wire:model="chamber_form_secondary_phone" class="w-full border rounded p-2 text-xs" placeholder="+880 1711-xxxxxx">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700">WhatsApp Hotline</label>
                            <input type="text" wire:model="chamber_form_whatsapp_number" class="w-full border rounded p-2 text-xs" placeholder="+8801819xxxxxx">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Visiting Hours / Seat Times</label>
                        <input type="text" wire:model="chamber_form_visiting_hours" class="w-full border rounded p-2 text-xs" placeholder="5 PM - 9 PM (Sat-Wed)">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 border-t pt-3">
                    <button type="button" wire:click="closeChamberModal" class="px-4 py-2 border rounded text-xs font-bold text-gray-600 hover:bg-gray-100">Cancel</button>
                    <button type="button" wire:click="saveChamber" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded text-xs font-bold shadow-sm">Save Hospital Profile</button>
                </div>
            </div>
        </div>
    @endif
</div>
