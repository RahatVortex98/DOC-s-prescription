<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppConversation extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_WAITING_HUMAN = 'waiting_human';

    public const STATUS_RESOLVED = 'resolved';

    public const STEP_IDLE = 'IDLE';

    public const STEP_AWAITING_NAME = 'AWAITING_NAME';

    public const STEP_AWAITING_CHAMBER = 'AWAITING_CHAMBER';

    public const STEP_AWAITING_DATE = 'AWAITING_DATE';

    public const STEP_AWAITING_SLOT = 'AWAITING_SLOT';

    public const STEP_AWAITING_CONFIRMATION = 'AWAITING_CONFIRMATION';

    public const STEP_CONFIRMED = 'CONFIRMED';

    public const STEP_HUMAN_TAKEOVER = 'HUMAN_TAKEOVER';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'integration_id',
        'patient_id',
        'wa_id',
        'status',
        'current_step',
        'state_data',
        'assigned_user_id',
        'last_inbound_at',
        'last_message_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state_data' => 'array',
            'last_inbound_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    public function isHumanTakeover(): bool
    {
        return $this->current_step === self::STEP_HUMAN_TAKEOVER || $this->status === self::STATUS_WAITING_HUMAN;
    }

    public function toggleHumanTakeover(bool $enable, ?int $userId = null): void
    {
        $this->current_step = $enable ? self::STEP_HUMAN_TAKEOVER : self::STEP_IDLE;
        $this->status = $enable ? self::STATUS_WAITING_HUMAN : self::STATUS_ACTIVE;
        $this->assigned_user_id = $enable ? ($userId ?? auth()->id()) : null;
        $this->save();
    }

    public function resetToIdle(): void
    {
        $this->current_step = self::STEP_IDLE;
        $this->state_data = null;
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * @return BelongsTo<WhatsAppIntegration, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(WhatsAppIntegration::class, 'integration_id');
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * @return HasMany<WhatsAppMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id')->orderBy('created_at');
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'conversation_id');
    }
}
