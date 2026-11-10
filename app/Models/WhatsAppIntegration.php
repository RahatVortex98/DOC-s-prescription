<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppIntegration extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_DISCONNECTED = 'disconnected';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_FAILED = 'failed';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'whatsapp_integrations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'waba_id',
        'phone_number_id',
        'display_phone_number',
        'access_token',
        'token_expires_at',
        'status',
        'connected_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED && ! empty($this->phone_number_id);
    }

    public function isExpired(): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return HasMany<WhatsAppConversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(WhatsAppConversation::class, 'integration_id');
    }
}
