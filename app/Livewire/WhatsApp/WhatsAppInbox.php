<?php

namespace App\Livewire\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppIntegration;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('WhatsApp Clinical Inbox - BikrifyCare')]
class WhatsAppInbox extends Component
{
    public ?int $selected_conversation_id = null;

    public string $search_query = '';

    public string $status_filter = 'all'; // all, waiting_human, active, resolved

    public string $reply_text = '';

    public ?string $feedback_message = null;

    public ?string $error_message = null;

    public function mount(?int $conversationId = null): void
    {
        $tenantId = auth()->user()?->tenant_id;
        if (! $tenantId) {
            abort(403, 'Tenant account not found.');
        }

        if ($conversationId) {
            $this->selectConversation($conversationId);
        } else {
            // Select the most recent conversation by default
            $first = WhatsAppConversation::orderByDesc('last_message_at')
                ->orderByDesc('updated_at')
                ->first();

            if ($first) {
                $this->selected_conversation_id = $first->id;
            }
        }
    }

    public function selectConversation(int $id): void
    {
        $conversation = WhatsAppConversation::find($id);
        if (! $conversation) {
            $this->error_message = 'Conversation not found or access denied.';

            return;
        }

        $this->selected_conversation_id = $conversation->id;
        $this->reply_text = '';
        $this->feedback_message = null;
        $this->error_message = null;
    }

    public function sendManualReply(): void
    {
        $this->validate([
            'reply_text' => 'required|string|max:4000',
        ]);

        if (! $this->selected_conversation_id) {
            return;
        }

        $tenantId = auth()->user()?->tenant_id;
        $conversation = WhatsAppConversation::find($this->selected_conversation_id);
        if (! $conversation) {
            $this->error_message = 'Conversation not found.';

            return;
        }

        $integration = WhatsAppIntegration::where('tenant_id', $tenantId)->first();
        if (! $integration || ! $integration->isConnected()) {
            $this->error_message = 'Cannot send message: WhatsApp Business account is not connected. Visit Settings to connect.';

            return;
        }

        try {
            $text = trim($this->reply_text);

            SendWhatsAppMessage::dispatch(
                $integration->id,
                $conversation->id,
                $conversation->wa_id,
                'text',
                ['text' => $text]
            );

            // Automatically switch conversation to Human Takeover mode so the bot doesn't interfere
            if (! $conversation->isHumanTakeover()) {
                $conversation->toggleHumanTakeover(true, auth()->id());
            }

            $this->reply_text = '';
            $this->feedback_message = 'Message dispatched via Meta Cloud API.';
            $this->error_message = null;
        } catch (Exception $e) {
            $this->error_message = 'Failed to send reply: '.$e->getMessage();
        }
    }

    public function toggleTakeover(bool $enable): void
    {
        if (! $this->selected_conversation_id) {
            return;
        }

        $conversation = WhatsAppConversation::find($this->selected_conversation_id);
        if (! $conversation) {
            return;
        }

        $conversation->toggleHumanTakeover($enable, auth()->id());

        $this->feedback_message = $enable
            ? '🤝 Switched to Staff Takeover mode. Bot automated replies are paused for this patient.'
            : '🤖 Switched to Automated Chatbot mode.';
    }

    public function markResolved(): void
    {
        if (! $this->selected_conversation_id) {
            return;
        }

        $conversation = WhatsAppConversation::find($this->selected_conversation_id);
        if (! $conversation) {
            return;
        }

        $conversation->update([
            'status' => WhatsAppConversation::STATUS_RESOLVED,
            'current_step' => WhatsAppConversation::STEP_IDLE,
        ]);

        $this->feedback_message = 'Conversation marked as resolved.';
    }

    /**
     * @return Collection<int, WhatsAppConversation>
     */
    public function getConversationsProperty(): Collection
    {
        return WhatsAppConversation::with(['patient', 'messages'])
            ->when($this->search_query !== '', function ($q) {
                $search = trim($this->search_query);
                $q->where(function ($sub) use ($search) {
                    $sub->where('wa_id', 'like', "%{$search}%")
                        ->orWhereHas('patient', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($this->status_filter === 'waiting_human', fn ($q) => $q->where('status', WhatsAppConversation::STATUS_WAITING_HUMAN))
            ->when($this->status_filter === 'active', fn ($q) => $q->where('status', WhatsAppConversation::STATUS_ACTIVE))
            ->when($this->status_filter === 'resolved', fn ($q) => $q->where('status', WhatsAppConversation::STATUS_RESOLVED))
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function render(): View
    {
        $selectedConversation = $this->selected_conversation_id
            ? WhatsAppConversation::with(['patient', 'messages', 'appointments' => fn ($q) => $q->latest()])
                ->find($this->selected_conversation_id)
            : null;

        $integration = WhatsAppIntegration::where('tenant_id', auth()->user()?->tenant_id)->first();

        return view('livewire.whatsapp.whatsapp-inbox', [
            'conversations' => $this->conversations,
            'selectedConversation' => $selectedConversation,
            'integration' => $integration,
        ])->layout('layouts.app');
    }
}
