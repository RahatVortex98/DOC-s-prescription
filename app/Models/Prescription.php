<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'prescription_no',
        'patient_id',
        'chamber_id',
        'doctor_name',
        'doctor_qualification',
        'doctor_designation',
        'chamber_name',
        'chamber_address',
        'chamber_phone',
        'chamber_visiting_hours',
        'prescription_date',
        'chief_complaints',
        'clinical_findings',
        'diagnosis',
        'investigations',
        'advice',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'prescription_date' => 'date',
        ];
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
