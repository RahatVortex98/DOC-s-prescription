<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chamber extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'hospital_affiliation',
        'doctor_name',
        'doctor_qualification',
        'doctor_designation',
        'specialty',
        'address',
        'room_number',
        'phone',
        'secondary_phone',
        'whatsapp_number',
        'visiting_hours',
        'is_default',
    ];

    protected static function booted(): void
    {
        static::creating(function (Chamber $chamber) {
            $chamber->doctor_name = $chamber->doctor_name ?: 'Doctor';
            $chamber->doctor_qualification = $chamber->doctor_qualification ?: 'MBBS';
            $chamber->doctor_designation = $chamber->doctor_designation ?: 'Consultant';
            $chamber->specialty = $chamber->specialty ?: 'General Medicine';
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
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
}
