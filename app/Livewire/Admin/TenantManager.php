<?php

namespace App\Livewire\Admin;

use App\Models\Chamber;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tenant Management - Super Admin')]
#[Layout('layouts.app')]
class TenantManager extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingTenantId = null;

    // Doctor profile & credentials (managed permanently by Super Admin)
    public string $doctor_name = '';

    public string $slug = '';

    public string $bmdc_no = '';

    public string $doctor_qualification = '';

    public string $doctor_designation = '';

    public string $specialty = 'General Medicine';

    public string $doctor_description = '';

    public string $brand_color = '#0891b2';

    public string $logo_path = '';

    public string $signature_path = '';

    public string $status = 'trial';

    public string $doctor_email = '';

    public string $doctor_password = '';

    public ?string $feedbackMessage = null;

    public function openCreateModal(): void
    {
        $this->reset([
            'editingTenantId',
            'doctor_name',
            'slug',
            'bmdc_no',
            'doctor_qualification',
            'doctor_designation',
            'specialty',
            'doctor_description',
            'brand_color',
            'logo_path',
            'signature_path',
            'status',
            'doctor_email',
            'doctor_password',
            'feedbackMessage',
        ]);
        $this->status = 'trial';
        $this->specialty = 'General Medicine';
        $this->brand_color = '#0891b2';
        $this->showEditModal = false;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function openEditModal(int $tenantId): void
    {
        $tenant = Tenant::with('users')->findOrFail($tenantId);

        $this->editingTenantId = $tenant->id;
        $this->doctor_name = $tenant->doctor_name ?? '';
        $this->slug = $tenant->slug ?? '';
        $this->bmdc_no = $tenant->bmdc_no ?? '';
        $this->doctor_qualification = $tenant->doctor_qualification ?? '';
        $this->doctor_designation = $tenant->doctor_designation ?? '';
        $this->specialty = $tenant->specialty ?? 'General Medicine';
        $this->doctor_description = $tenant->doctor_description ?? '';
        $this->brand_color = $tenant->brand_color ?? '#0891b2';
        $this->logo_path = $tenant->logo_path ?? '';
        $this->signature_path = $tenant->signature_path ?? '';
        $this->status = $tenant->status ?? 'active';

        $doctorUser = $tenant->users->where('role', 'doctor')->first();
        $this->doctor_email = $doctorUser ? $doctorUser->email : '';
        $this->doctor_password = '';

        $this->showCreateModal = false;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingTenantId = null;
    }

    public function saveTenant(): void
    {
        $this->validate([
            'doctor_name' => 'required|string|max:150',
            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9._-]+$/',
                'unique:tenants,slug',
                Rule::notIn(Tenant::RESERVED_SLUGS),
            ],
            'bmdc_no' => 'nullable|string|max:50',
            'doctor_qualification' => 'nullable|string|max:255',
            'doctor_designation' => 'nullable|string|max:255',
            'specialty' => 'required|string|max:150',
            'doctor_description' => 'nullable|string|max:2000',
            'brand_color' => 'nullable|string|max:20',
            'logo_path' => 'nullable|string|max:255',
            'signature_path' => 'nullable|string|max:255',
            'status' => 'required|in:trial,active,suspended',
            'doctor_email' => 'required|email|unique:users,email',
            'doctor_password' => 'required|string|min:8',
        ], [
            'slug.not_in' => 'The chosen slug is a reserved system path. Please choose another.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, dashes, underscores, and dots.',
        ]);

        DB::transaction(function () {
            $tenant = Tenant::create([
                'doctor_name' => $this->doctor_name,
                'slug' => strtolower(trim($this->slug)),
                'bmdc_no' => $this->bmdc_no ?: null,
                'doctor_qualification' => $this->doctor_qualification ?: null,
                'doctor_designation' => $this->doctor_designation ?: null,
                'specialty' => $this->specialty,
                'doctor_description' => $this->doctor_description ?: null,
                'brand_color' => $this->brand_color ?: '#0891b2',
                'logo_path' => $this->logo_path ?: null,
                'signature_path' => $this->signature_path ?: null,
                'status' => $this->status,
            ]);

            User::create([
                'name' => $this->doctor_name,
                'email' => $this->doctor_email,
                'password' => Hash::make($this->doctor_password),
                'tenant_id' => $tenant->id,
                'role' => 'doctor',
            ]);

            // Create initial main chamber for this doctor with Super Admin verified credentials
            Chamber::withoutGlobalScope('tenant')->create([
                'tenant_id' => $tenant->id,
                'name' => 'Main Consultation Chamber',
                'doctor_name' => $tenant->doctor_name,
                'doctor_qualification' => $tenant->doctor_qualification,
                'doctor_designation' => $tenant->doctor_designation,
                'specialty' => $tenant->specialty,
                'is_default' => true,
            ]);
        });

        $this->feedbackMessage = "Tenant clinic '{$this->doctor_name}' ({$this->slug}) created and provisioned permanently by Super Admin.";
        $this->showCreateModal = false;
    }

    public function updateTenant(): void
    {
        if (! $this->editingTenantId) {
            return;
        }

        $tenant = Tenant::findOrFail($this->editingTenantId);

        $this->validate([
            'doctor_name' => 'required|string|max:150',
            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('tenants', 'slug')->ignore($tenant->id),
                Rule::notIn(Tenant::RESERVED_SLUGS),
            ],
            'bmdc_no' => 'nullable|string|max:50',
            'doctor_qualification' => 'nullable|string|max:255',
            'doctor_designation' => 'nullable|string|max:255',
            'specialty' => 'required|string|max:150',
            'doctor_description' => 'nullable|string|max:2000',
            'brand_color' => 'nullable|string|max:20',
            'logo_path' => 'nullable|string|max:255',
            'signature_path' => 'nullable|string|max:255',
            'status' => 'required|in:trial,active,suspended',
            'doctor_password' => 'nullable|string|min:8',
        ]);

        DB::transaction(function () use ($tenant) {
            $tenant->update([
                'doctor_name' => $this->doctor_name,
                'slug' => strtolower(trim($this->slug)),
                'bmdc_no' => $this->bmdc_no ?: null,
                'doctor_qualification' => $this->doctor_qualification ?: null,
                'doctor_designation' => $this->doctor_designation ?: null,
                'specialty' => $this->specialty,
                'doctor_description' => $this->doctor_description ?: null,
                'brand_color' => $this->brand_color ?: '#0891b2',
                'logo_path' => $this->logo_path ?: null,
                'signature_path' => $this->signature_path ?: null,
                'status' => $this->status,
            ]);

            // Sync user name and optional password
            $doctorUser = $tenant->users()->where('role', 'doctor')->first();
            if ($doctorUser) {
                $userData = ['name' => $this->doctor_name];
                if (! empty($this->doctor_password)) {
                    $userData['password'] = Hash::make($this->doctor_password);
                }
                $doctorUser->update($userData);
            }

            // Sync verified credentials into default chamber
            $defaultChamber = Chamber::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('is_default', true)
                ->first();

            if ($defaultChamber) {
                $defaultChamber->update([
                    'doctor_name' => $tenant->doctor_name,
                    'doctor_qualification' => $tenant->doctor_qualification,
                    'doctor_designation' => $tenant->doctor_designation,
                    'specialty' => $tenant->specialty,
                ]);
            }
        });

        $this->feedbackMessage = "Doctor profile & description for '{$this->doctor_name}' permanently updated by Super Admin.";
        $this->showEditModal = false;
        $this->editingTenantId = null;
    }

    public function toggleStatus(int $tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->status = $tenant->status === 'suspended' ? 'active' : 'suspended';
        $tenant->save();

        $this->feedbackMessage = "Tenant '{$tenant->doctor_name}' is now {$tenant->status}.";
    }

    public function render(): View
    {
        $tenants = Tenant::withCount(['users', 'chambers', 'prescriptions', 'patients'])
            ->latest()
            ->get();

        return view('livewire.admin.tenant-manager', [
            'tenants' => $tenants,
        ]);
    }
}
