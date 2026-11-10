<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Prescription extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'public_id',
        'prescription_no',
        'patient_id',
        'chamber_id',
        'doctor_name',
        'doctor_qualification',
        'doctor_designation',
        'doctor_bmdc_no',
        'chamber_name',
        'chamber_address',
        'chamber_phone',
        'chamber_visiting_hours',
        'prescription_date',
        'status',
        'voided_at',
        'void_reason',
        'chief_complaints',
        'clinical_findings',
        'diagnosis',
        'investigations',
        'advice',
    ];

    protected static function booted(): void
    {
        static::creating(function (Prescription $prescription) {
            $prescription->public_id ??= (string) Str::ulid();
            $prescription->status ??= 'active';

            if (empty($prescription->chamber_name) && $prescription->chamber_id) {
                $chamber = Chamber::withoutGlobalScope('tenant')->find($prescription->chamber_id);
                if ($chamber) {
                    $prescription->chamber_name = $chamber->name;
                    $prescription->chamber_address ??= $chamber->address;
                    $prescription->chamber_phone ??= $chamber->phone;
                    $prescription->chamber_visiting_hours ??= $chamber->visiting_hours;
                }
            }

            if (empty($prescription->chamber_name)) {
                $prescription->chamber_name = 'Main Chamber';
            }

            if (empty($prescription->doctor_name) && auth()->check() && auth()->user()?->tenant) {
                $prescription->doctor_name = auth()->user()->tenant->doctor_name;
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'prescription_date' => 'date',
            'voided_at' => 'datetime',
        ];
    }

    public function isVoided(): bool
    {
        return $this->status === 'void';
    }

    public function void(string $reason): void
    {
        $this->update([
            'status' => 'void',
            'voided_at' => now(),
            'void_reason' => $reason,
        ]);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<Chamber, $this>
     */
    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }

    /**
     * @return HasMany<PrescriptionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
