<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-xs font-black bg-purple-100 text-purple-800 uppercase tracking-wider">Super Admin Portal</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1">Tenant Clinics & Doctor Credentialing</h1>
            <p class="text-sm text-slate-500">Permanently configure verified doctor identities, BMDC registrations, specialties, and institutional descriptions.</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="bg-purple-700 hover:bg-purple-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-sm flex items-center gap-1.5 transition"
        >
            <span>+</span> Provision New Doctor Clinic
        </button>
    </div>

    @if ($feedbackMessage)
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold flex items-center justify-between">
            <span>{{ $feedbackMessage }}</span>
            <button wire:click="$set('feedbackMessage', null)" class="text-emerald-500 hover:text-emerald-800 font-bold">✕</button>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="py-3 px-4">Doctor & Credentials</th>
                    <th class="py-3 px-4">Specialty & Description</th>
                    <th class="py-3 px-4">Public URL</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Metrics</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($tenants as $tenant)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span>{{ $tenant->doctor_name }}</span>
                                @if($tenant->bmdc_no)
                                    <span class="text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-mono font-semibold">BMDC: {{ $tenant->bmdc_no }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $tenant->doctor_qualification ?: 'Qualifications not set' }}</div>
                            @if($tenant->doctor_designation)
                                <div class="text-xs text-purple-700 font-medium">{{ $tenant->doctor_designation }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 max-w-xs">
                            <span class="inline-block px-2 py-0.5 bg-cyan-50 text-cyan-800 border border-cyan-200 rounded text-xs font-bold">
                                {{ $tenant->specialty ?: 'General Medicine' }}
                            </span>
                            @if($tenant->doctor_description)
                                <p class="text-xs text-slate-500 truncate mt-1" title="{{ $tenant->doctor_description }}">
                                    {{ Str::limit($tenant->doctor_description, 60) }}
                                </p>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs">
                            <a href="{{ route('appointment.public_book', $tenant->slug) }}" target="_blank" class="text-cyan-700 hover:underline flex items-center gap-1 font-semibold">
                                <span>/{{ $tenant->slug }}</span>
                                <span class="text-[10px]">↗</span>
                            </a>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($tenant->status === 'active')
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Active</span>
                            @elseif($tenant->status === 'trial')
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Trial</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Suspended</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-xs text-slate-600">
                            <span class="font-semibold">{{ $tenant->users_count }}</span> users ·
                            <span class="font-semibold">{{ $tenant->chambers_count }}</span> chambers ·
                            <span class="font-semibold">{{ $tenant->prescriptions_count }}</span> Rx
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1">
                            <button
                                wire:click="openEditModal({{ $tenant->id }})"
                                class="text-xs font-bold px-2.5 py-1.5 rounded border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 transition"
                                title="Permanently Edit Doctor Profile"
                            >
                                ⚙️ Edit Profile
                            </button>
                            <button
                                wire:click="toggleStatus({{ $tenant->id }})"
                                class="text-xs font-bold px-2.5 py-1.5 rounded border transition @if($tenant->status === 'suspended') bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 @else bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100 @endif"
                            >
                                {{ $tenant->status === 'suspended' ? 'Reactivate' : 'Suspend' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 text-sm">
                            No doctor clinics registered yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create / Edit Doctor Profile Modal (Super Admin Exclusive) --}}
    @if ($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full border border-slate-200 overflow-hidden my-8">
                <div class="bg-purple-800 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base">
                            {{ $showEditModal ? 'Edit Doctor Profile & Credentials (Super Admin)' : 'Provision New Doctor Clinic' }}
                        </h3>
                        <p class="text-xs text-purple-200 mt-0.5">Permanent configuration set exclusively by Bikrify Super Admin.</p>
                    </div>
                    <button wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}" class="text-purple-200 hover:text-white font-bold text-lg">✕</button>
                </div>

                <form wire:submit.prevent="{{ $showEditModal ? 'updateTenant' : 'saveTenant' }}" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Doctor Full Name *</label>
                            <input type="text" wire:model="doctor_name" placeholder="e.g. Dr. Mahbubur Rahman" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                            @error('doctor_name') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">BMDC Reg. No.</label>
                            <input type="text" wire:model="bmdc_no" placeholder="e.g. A-45210" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            @error('bmdc_no') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Public Subdomain / URL Slug *</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-xs font-mono text-slate-600">domain.com/</span>
                            <input type="text" wire:model="slug" placeholder="dr-mahbub" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-r-lg font-mono focus:ring-2 focus:ring-purple-600">
                        </div>
                        @error('slug') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Doctor Qualifications (Degrees)</label>
                            <input type="text" wire:model="doctor_qualification" placeholder="e.g. MBBS, FCPS (Medicine), MD, BCS (Health)" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            @error('doctor_qualification') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Institutional Designation</label>
                            <input type="text" wire:model="doctor_designation" placeholder="e.g. Associate Professor & Chief Consultant" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            @error('doctor_designation') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Primary Clinical Specialty *</label>
                            <select wire:model="specialty" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                                <option value="General Medicine">General Medicine</option>
                                <option value="Nephrology / CKD Specialist">Nephrology / CKD Specialist</option>
                                <option value="Cardiology">Cardiology</option>
                                <option value="Gastroenterology">Gastroenterology</option>
                                <option value="Diabetology / Endocrinology">Diabetology / Endocrinology</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="Gynecology & Obstetrics">Gynecology & Obstetrics</option>
                            </select>
                            @error('specialty') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Prescription Brand Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="brand_color" class="w-10 h-9 p-0 border border-slate-300 rounded-lg cursor-pointer">
                                <input type="text" wire:model="brand_color" placeholder="#0891b2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg font-mono">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Doctor Professional Description / Bio (Super Admin Only)
                        </label>
                        <textarea
                            wire:model="doctor_description"
                            rows="3"
                            placeholder="e.g. Senior Consultant at National Institute of Kidney Diseases & Urology. Over 18 years of clinical experience in advanced kidney care, dialysis, and hypertensive nephropathy."
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-600"
                        ></textarea>
                        <p class="text-[11px] text-slate-400 mt-0.5">This verified description is permanently locked and only editable by Super Admin.</p>
                        @error('doctor_description') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Logo URL / Image Path</label>
                            <input type="text" wire:model="logo_path" placeholder="e.g. /images/doctor-logo.png" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Signature Image Path</label>
                            <input type="text" wire:model="signature_path" placeholder="e.g. /images/signatures/doc-1.png" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Clinic Status *</label>
                            <select wire:model="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                                <option value="trial">Trial (Free 14 Days)</option>
                                <option value="active">Active (Paid Subscription)</option>
                                <option value="suspended">Suspended (Blocked Access)</option>
                            </select>
                            @error('status') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                {{ $showEditModal ? 'Reset Doctor Password (Optional)' : 'Initial Doctor Password *' }}
                            </label>
                            <input
                                type="password"
                                wire:model="doctor_password"
                                placeholder="{{ $showEditModal ? 'Leave blank to keep current' : '••••••••' }}"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"
                            >
                            @error('doctor_password') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if(! $showEditModal)
                        <div class="pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Doctor Login Email *</label>
                            <input type="email" wire:model="doctor_email" placeholder="doctor@clinic.com" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            @error('doctor_email') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Hospital & Chamber Profile (Pre-configured by Super Admin) -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Hospital / Chamber Practice Location (Bikrify Setup)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Hospital / Chamber Name</label>
                                <input type="text" wire:model="chamber_name" placeholder="e.g. Popular Diagnostic Center - Dhanmondi" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Room / Seat No.</label>
                                <input type="text" wire:model="room_number" placeholder="e.g. Room #405, 4th Floor" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Chamber Address / Location</label>
                            <input type="text" wire:model="chamber_address" placeholder="e.g. House 16, Road 2, Dhanmondi, Dhaka" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Serial Phone Hotline</label>
                                <input type="text" wire:model="chamber_phone" placeholder="e.g. +880 1819-xxxxxx" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Visiting Hours</label>
                                <input type="text" wire:model="chamber_visiting_hours" placeholder="e.g. 5 PM - 9 PM (Sat-Wed)" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-purple-700 hover:bg-purple-800 text-white font-bold px-5 py-2 text-sm rounded-lg shadow-sm transition"
                        >
                            {{ $showEditModal ? 'Update Doctor Profile Permanently' : 'Create & Provision Clinic' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
