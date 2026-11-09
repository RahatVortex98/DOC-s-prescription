<div class="max-w-7xl mx-auto py-6 px-4">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b mb-6">
        <div>
            <h1 class="text-2xl font-black text-cyan-800">Bikrify Prescription Studio</h1>
            <p class="text-sm text-gray-500">Fast, offline-ready medical prescribing platform</p>
        </div>
        <button wire:click="saveAndPrint" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition cursor-pointer">
            Save & Print A4 Pad
        </button>
    </div>

    <!-- Patient Header Bar -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase">Patient Name</label>
            <input type="text" wire:model="patient_name" class="mt-1 w-full border rounded-md p-2 text-sm" placeholder="e.g. Rahim Ahmed">
            @error('patient_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase">Age</label>
            <input type="number" wire:model="patient_age" class="mt-1 w-full border rounded-md p-2 text-sm" placeholder="e.g. 34">
            @error('patient_age') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase">Gender</label>
            <select wire:model="patient_gender" class="mt-1 w-full border rounded-md p-2 text-sm">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            @error('patient_gender') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase">Phone</label>
            <input type="text" wire:model="patient_phone" class="mt-1 w-full border rounded-md p-2 text-sm" placeholder="017xxxxxxxx">
        </div>
    </div>

    <!-- Dual Column Split Layout -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Left Sidebar: Clinical Notes -->
        <div class="md:col-span-4 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <h2 class="font-bold text-gray-700 mb-2 border-b pb-1 text-sm">Chief Complaints</h2>
                <textarea wire:model="chief_complaints" rows="3" class="w-full border rounded-md p-2 text-sm" placeholder="e.g. Generalized weakness, Fever x 3 days"></textarea>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <h2 class="font-bold text-gray-700 mb-2 border-b pb-1 text-sm">On Examination (O/E)</h2>
                <textarea wire:model="clinical_findings" rows="3" class="w-full border rounded-md p-2 text-sm" placeholder="Pulse: 78/min, BP: 120/80 mmHg"></textarea>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <h2 class="font-bold text-gray-700 mb-2 border-b pb-1 text-sm">Diagnosis</h2>
                <textarea wire:model="diagnosis" rows="2" class="w-full border rounded-md p-2 text-sm" placeholder="Gouty Arthritis, Gastritis"></textarea>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <h2 class="font-bold text-gray-700 mb-2 border-b pb-1 text-sm">Investigations</h2>
                <textarea wire:model="investigations" rows="2" class="w-full border rounded-md p-2 text-sm" placeholder="CBC, S. Creatinine, USG of whole abdomen"></textarea>
            </div>
        </div>

        <!-- Right Side: Rx & Dosage -->
        <div class="md:col-span-8 bg-white rounded-xl shadow-sm border p-5">
            <div class="flex items-center justify-between pb-3 border-b mb-4">
                <span class="text-3xl font-serif font-black text-cyan-900">℞</span>
                <div class="relative w-72">
                    <input type="text" wire:model.live.debounce.250ms="search" placeholder="Search 42,000+ medicines..." class="w-full border rounded-md px-3 py-1.5 text-sm">
                    @if(!empty($searchResults))
                        <div class="absolute z-50 bg-white border rounded-md shadow-lg w-full mt-1 max-h-56 overflow-y-auto">
                            @foreach($searchResults as $res)
                                <button type="button" wire:click="addItem({{ json_encode(['type' => $res['dosage_form'], 'name' => $res['brand_name'], 'generic' => $res['generic_name'], 'strength' => $res['strength'], 'dosage' => '1+0+1', 'duration' => '7 days', 'instruction' => 'After meal']) }})" class="w-full text-left px-3 py-2 text-xs hover:bg-cyan-50 border-b flex justify-between cursor-pointer">
                                    <span><strong>{{ $res['dosage_form'] }} {{ $res['brand_name'] }}</strong> ({{ $res['strength'] }})</span>
                                    <span class="text-gray-400 italic">{{ $res['generic_name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @error('items') <div class="text-xs text-red-500 mb-2">{{ $message }}</div> @enderror
            @error('items.*.name') <div class="text-xs text-red-500 mb-2">{{ $message }}</div> @enderror

            <!-- Medicine Item Rows -->
            <div class="space-y-3">
                @foreach($items as $i => $item)
                    <div class="p-3 border rounded-lg bg-gray-50 grid grid-cols-12 gap-2 items-center" wire:key="med-item-{{ $i }}">
                        <div class="col-span-2">
                            <input type="text" wire:model="items.{{ $i }}.type" class="w-full text-xs border rounded p-1.5 bg-white" placeholder="Form (Tab)">
                        </div>
                        <div class="col-span-4">
                            <input type="text" wire:model="items.{{ $i }}.name" class="w-full text-xs font-semibold border rounded p-1.5 bg-white" placeholder="Medicine Brand">
                        </div>
                        <div class="col-span-2">
                            <input type="text" wire:model="items.{{ $i }}.dosage" class="w-full text-xs border rounded p-1.5 bg-white" placeholder="1+0+1">
                        </div>
                        <div class="col-span-2">
                            <input type="text" wire:model="items.{{ $i }}.duration" class="w-full text-xs border rounded p-1.5 bg-white" placeholder="7 days">
                        </div>
                        <div class="col-span-2 flex items-center gap-1">
                            <input type="text" wire:model="items.{{ $i }}.instruction" class="w-full text-xs border rounded p-1.5 bg-white" placeholder="After meal">
                            <button type="button" wire:click="removeItem({{ $i }})" class="text-red-500 hover:text-red-700 px-1 font-bold cursor-pointer">×</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" wire:click="addItem" class="mt-4 text-xs font-bold text-cyan-700 hover:text-cyan-900 flex items-center gap-1 cursor-pointer">
                + Add Another Medicine Row
            </button>

            <div class="mt-6 border-t pt-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Special Advice</label>
                <textarea wire:model="advice" rows="2" class="w-full border rounded-md p-2 text-sm" placeholder="Avoid fatty foods, drink plenty of water, follow up after 7 days."></textarea>
            </div>
        </div>
    </div>
</div>
