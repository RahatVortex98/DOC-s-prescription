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

    public string $doctor_name = '';

    public string $slug = '';

    public string $bmdc_no = '';

    public string $status = 'trial';

    public string $doctor_email = '';

    public string $doctor_password = '';

    public ?string $feedbackMessage = null;

    public function openCreateModal(): void
    {
        $this->reset(['doctor_name', 'slug', 'bmdc_no', 'status', 'doctor_email', 'doctor_password', 'feedbackMessage']);
        $this->status = 'trial';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
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
                'status' => $this->status,
            ]);

            User::create([
                'name' => $this->doctor_name,
                'email' => $this->doctor_email,
                'password' => Hash::make($this->doctor_password),
                'tenant_id' => $tenant->id,
                'role' => 'doctor',
            ]);

            Chamber::withoutGlobalScope('tenant')->create([
                'tenant_id' => $tenant->id,
                'name' => 'Main Chamber',
                'doctor_name' => $tenant->doctor_name,
                'specialty' => 'General Medicine',
                'is_default' => true,
            ]);
        });

        $this->feedbackMessage = "Tenant clinic '{$this->doctor_name}' ({$this->slug}) created successfully.";
        $this->showCreateModal = false;
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
