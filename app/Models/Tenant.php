<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    /**
     * Reserved slug paths that cannot be claimed by a tenant.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = [
        'app',
        'login',
        'logout',
        'livewire',
        'admin',
        'api',
        'up',
        'build',
        'storage',
        'book',
        'rx',
        'audit-logs',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'doctor_name',
        'bmdc_no',
        'status',
        'doctor_qualification',
        'doctor_designation',
        'specialty',
        'doctor_description',
        'brand_color',
        'logo_path',
        'signature_path',
    ];

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Chamber, $this>
     */
    public function chambers(): HasMany
    {
        return $this->hasMany(Chamber::class);
    }

    /**
     * @return HasMany<Patient, $this>
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * @return HasMany<Prescription, $this>
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return HasMany<PrescriptionTemplate, $this>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(PrescriptionTemplate::class);
    }

    /**
     * @return HasMany<DrugKit, $this>
     */
    public function drugKits(): HasMany
    {
        return $this->hasMany(DrugKit::class);
    }
}
