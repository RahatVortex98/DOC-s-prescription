<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-xs font-black bg-purple-100 text-purple-800 uppercase tracking-wider">Super Admin Portal</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1">Tenant & Doctor Provisioning</h1>
            <p class="text-sm text-slate-500">Manage doctor clinics, assign subscription statuses, and provision credentials.</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="bg-purple-700 hover:bg-purple-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-sm flex items-center gap-1.5 transition"
        >
            <span>+</span> Provision New Tenant
        </button>
    </div>

    @if ($feedbackMessage)
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold flex items-center justify-between">
            <span>{{ $feedbackMessage }}</span>
            <button wire:click="$set('feedbackMessage', null)" class="text-emerald-500 hover:text-emerald-800">✕</button>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="py-3 px-4">Clinic / Doctor</th>
                    <th class="py-3 px-4">Public Slug & Link</th>
                    <th class="py-3 px-4">BMDC Reg.</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Metrics</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($tenants as $tenant)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $tenant->doctor_name }}</div>
                            <div class="text-xs text-slate-500">Created {{ $tenant->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs">
                            <a href="{{ route('appointment.public_book', $tenant->slug) }}" target="_blank" class="text-cyan-700 hover:underline flex items-center gap-1">
                                <span>/{{ $tenant->slug }}</span>
                                <span class="text-[10px]">↗</span>
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-slate-600">
                            {{ $tenant->bmdc_no ?: '—' }}
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
                        <td class="py-3.5 px-4 text-right">
                            <button
                                wire:click="toggleStatus({{ $tenant->id }})"
                                class="text-xs font-bold px-3 py-1.5 rounded border transition @if($tenant->status === 'suspended') bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 @else bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100 @endif"
                            >
                                {{ $tenant->status === 'suspended' ? 'Reactivate' : 'Suspend' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 text-sm">
                            No tenant clinics registered yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create Tenant Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden">
                <div class="bg-purple-800 px-6 py-4 text-white flex items-center justify-between">
                    <h3 class="font-bold text-base">Provision New Doctor Clinic</h3>
                    <button wire:click="closeCreateModal" class="text-purple-200 hover:text-white font-bold">✕</button>
                </div>

                <form wire:submit.prevent="saveTenant" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Doctor Full Name</label>
                        <input type="text" wire:model="doctor_name" placeholder="Prof. Dr. Mahbubur Rahman" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                        @error('doctor_name') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Public URL Slug</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-xs font-mono text-slate-600">prescription.bikrify.com/</span>
                            <input type="text" wire:model="slug" placeholder="dr-mahbub" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-r-lg font-mono focus:ring-2 focus:ring-purple-600">
                        </div>
                        @error('slug') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">BMDC Reg. No.</label>
                            <input type="text" wire:model="bmdc_no" placeholder="A-45210" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            @error('bmdc_no') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Initial Status</label>
                            <select wire:model="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                                <option value="trial">Trial</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            @error('status') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <h4 class="text-xs font-black uppercase text-purple-900 tracking-wider mb-2">Doctor Login Account</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Doctor Email</label>
                                <input type="email" wire:model="doctor_email" placeholder="doctor@hospital.com" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                                @error('doctor_email') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Initial Password</label>
                                <input type="password" wire:model="doctor_password" placeholder="••••••••" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                                @error('doctor_password') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-2">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">
                            Cancel
                        </button>
                        <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white font-bold px-4 py-2 text-sm rounded-lg shadow-sm">
                            Create & Provision
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
