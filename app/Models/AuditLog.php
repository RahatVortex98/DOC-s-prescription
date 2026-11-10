<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'ip',
        'details',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Quick helper to record clinical audit log.
     *
     * @param  array<string, mixed>|null  $details
     */
    public static function record(string $action, ?Model $subject = null, ?array $details = null): ?self
    {
        $tenantId = auth()->user()?->tenant_id;
        if (! $tenantId && $subject && isset($subject->tenant_id)) {
            $tenantId = $subject->tenant_id;
        }

        if (! $tenantId) {
            return null;
        }

        return static::withoutGlobalScope('tenant')->create([
            'tenant_id' => $tenantId,
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'ip' => request()->ip(),
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}
