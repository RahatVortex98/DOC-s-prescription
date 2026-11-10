<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookEvent extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_FAILED = 'failed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_key',
        'tenant_id',
        'event_type',
        'payload',
        'processing_status',
        'attempts',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function isProcessed(): bool
    {
        return $this->processing_status === self::STATUS_PROCESSED;
    }

    public function markProcessed(): void
    {
        $this->update([
            'processing_status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $payload = $this->payload ?? [];
        $payload['last_error'] = $error;

        $this->update([
            'processing_status' => self::STATUS_FAILED,
            'attempts' => $this->attempts + 1,
            'payload' => $payload,
        ]);
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
