<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6">
    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-xl shadow-xs">
                💬
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    WhatsApp Patient Inbox
                    @if($integration && $integration->isConnected())
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ $integration->display_phone_number }}
                        </span>
                    @else
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                            Disconnected
                        </span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500">Live two-way WhatsApp patient communication and front-desk takeover desk.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('whatsapp.settings') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                <span>⚙️</span> WhatsApp Settings
            </a>
            <a href="{{ route('appointment.manager') }}" class="px-3 py-1.5 bg-[#1e3fae] hover:bg-[#173087] text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                <span>📋</span> Queue Desk
            </a>
        </div>
    </div>

    @if($feedback_message)
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <span>{{ $feedback_message }}</span>
            <button wire:click="$set('feedback_message', null)" class="text-emerald-500 hover:text-emerald-800 font-bold">✕</button>
        </div>
    @endif

    @if($error_message)
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <span>{{ $error_message }}</span>
            <button wire:click="$set('error_message', null)" class="text-rose-500 hover:text-rose-800 font-bold">✕</button>
        </div>
    @endif

    <!-- Two-Pane Inbox -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden grid grid-cols-1 md:grid-cols-12 min-h-[680px]">
        <!-- Left Pane: Conversations List (4 Cols) -->
        <div class="md:col-span-4 border-r border-slate-200 flex flex-col h-full bg-slate-50/50">
            <!-- Search & Filter Controls -->
            <div class="p-3.5 border-b border-slate-200 bg-white space-y-2.5">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search_query"
                    placeholder="Search patient name or mobile..."
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#1e3fae] focus:outline-none"
                >

                <div class="flex items-center gap-1 overflow-x-auto pb-1 text-[11px] font-bold">
                    <button
                        type="button"
                        wire:click="$set('status_filter', 'all')"
                        class="px-2.5 py-1 rounded-lg transition cursor-pointer @if($status_filter === 'all') bg-slate-800 text-white @else bg-slate-100 text-slate-600 hover:bg-slate-200 @endif"
                    >
                        All
                    </button>
                    <button
                        type="button"
                        wire:click="$set('status_filter', 'waiting_human')"
                        class="px-2.5 py-1 rounded-lg transition cursor-pointer flex items-center gap-1 @if($status_filter === 'waiting_human') bg-amber-500 text-white @else bg-amber-50 text-amber-800 border border-amber-200 @endif"
                    >
                        <span>🤝 Staff Needed</span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('status_filter', 'active')"
                        class="px-2.5 py-1 rounded-lg transition cursor-pointer @if($status_filter === 'active') bg-blue-600 text-white @else bg-blue-50 text-blue-700 @endif"
                    >
                        🤖 Bot Active
                    </button>
                </div>
            </div>

            <!-- Conversations List -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 max-h-[620px]">
                @forelse($conversations as $conv)
                    <div
                        wire:key="conv-{{ $conv->id }}"
                        wire:click="selectConversation({{ $conv->id }})"
                        class="p-3.5 hover:bg-white transition cursor-pointer @if($selected_conversation_id === $conv->id) bg-white border-l-4 border-l-[#1e3fae] shadow-xs @endif"
                    >
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <span class="font-bold text-xs text-slate-900 truncate">
                                {{ $conv->patient?->name ?: ($conv->state_data['patient_name'] ?? $conv->wa_id) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">
                                {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : '' }}
                            </span>
                        </div>

                        <div class="text-[11px] text-slate-500 truncate mb-1.5 font-mono">
                            {{ $conv->wa_id }}
                        </div>

                        <div class="flex items-center justify-between gap-1">
                            @php
                                $lastMsg = $conv->messages->last();
                            @endphp
                            <span class="text-[11px] text-slate-600 truncate max-w-[180px]">
                                {{ $lastMsg?->message_body ?: 'No messages yet' }}
                            </span>

                            @if($conv->isHumanTakeover())
                                <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">
                                    Staff Mode
                                </span>
                            @elseif($conv->status === 'resolved')
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                                    Resolved
                                </span>
                            @else
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">
                                    Bot ({{ $conv->current_step }})
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 px-4 text-xs text-slate-400">
                        No conversations found matching filters.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Pane: Active Chat Thread (8 Cols) -->
        <div class="md:col-span-8 flex flex-col h-full bg-white">
            @if($selectedConversation)
                <!-- Chat Header -->
                <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-800 font-bold flex items-center justify-center text-sm border border-emerald-500/20">
                            {{ mb_substr($selectedConversation->patient?->name ?: $selectedConversation->wa_id, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                {{ $selectedConversation->patient?->name ?: ($selectedConversation->state_data['patient_name'] ?? 'Patient') }}
                                <span class="text-xs font-mono font-medium text-slate-500">({{ $selectedConversation->wa_id }})</span>
                            </h2>
                            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                <span>Status:</span>
                                @if($selectedConversation->isHumanTakeover())
                                    <span class="text-amber-700 font-bold">🤝 Front-Desk Manual Takeover Active</span>
                                @else
                                    <span class="text-blue-700 font-bold">🤖 Automated Chatbot Running ({{ $selectedConversation->current_step }})</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($selectedConversation->isHumanTakeover())
                            <button
                                type="button"
                                wire:click="toggleTakeover(false)"
                                class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                title="Resume automated booking bot"
                            >
                                <span>🤖 Return to Bot</span>
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="toggleTakeover(true)"
                                class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                title="Pause automated bot and reply manually"
                            >
                                <span>🤝 Take Over Chat</span>
                            </button>
                        @endif

                        <button
                            type="button"
                            wire:click="markResolved"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition cursor-pointer"
                        >
                            ✓ Resolve
                        </button>
                    </div>
                </div>

                <!-- Chat Messages Stream -->
                <div class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/70 max-h-[480px]">
                    @forelse($selectedConversation->messages as $msg)
                        @if($msg->isInbound())
                            <!-- Inbound (From Patient) -->
                            <div class="flex items-start gap-2 max-w-lg">
                                <div class="w-6 h-6 rounded-full bg-slate-200 text-[10px] font-bold text-slate-700 flex items-center justify-center shrink-0">
                                    👤
                                </div>
                                <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-xs p-3 text-xs text-slate-800 shadow-xs space-y-1">
                                    <p class="whitespace-pre-line leading-relaxed">{{ $msg->message_body }}</p>
                                    <span class="text-[9px] text-slate-400 block text-right font-medium">
                                        {{ $msg->received_at ? $msg->received_at->format('h:i A') : '' }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <!-- Outbound (From Clinic / Bot) -->
                            <div class="flex items-end justify-end gap-2 ml-auto max-w-lg">
                                <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-2xl rounded-tr-xs p-3 text-xs shadow-xs space-y-1">
                                    <p class="whitespace-pre-line leading-relaxed">{{ $msg->message_body }}</p>
                                    <div class="flex items-center justify-end gap-1 text-[9px] text-emerald-100 font-medium">
                                        <span>{{ $msg->sent_at ? $msg->sent_at->format('h:i A') : '' }}</span>
                                        @if($msg->delivery_status === 'read')
                                            <span class="text-emerald-200 font-bold">✓✓ Read</span>
                                        @elseif($msg->delivery_status === 'delivered')
                                            <span>✓✓ Delivered</span>
                                        @else
                                            <span>✓ Sent</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-16 text-slate-400 text-xs">
                            No messages recorded in this conversation yet.
                        </div>
                    @endforelse
                </div>

                <!-- Manual Reply Bar -->
                <div class="p-3 border-t border-slate-200 bg-white">
                    <form wire:submit.prevent="sendManualReply" class="space-y-2">
                        <div class="flex items-end gap-2">
                            <textarea
                                wire:model="reply_text"
                                rows="2"
                                placeholder="Type manual WhatsApp reply to patient..."
                                class="flex-1 px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                            <button
                                type="submit"
                                class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 cursor-pointer shadow-md shrink-0"
                            >
                                <span>Send WhatsApp</span>
                                <span wire:loading wire:target="sendManualReply" class="animate-spin">⏳</span>
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>Sending a reply automatically pauses the bot and switches to Staff Takeover mode.</span>
                            <span>24-Hour WhatsApp Service Window Active</span>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-3xl mb-3">
                        💬
                    </div>
                    <h3 class="text-sm font-bold text-slate-700 mb-1">Select a Conversation</h3>
                    <p class="text-xs text-slate-400 max-w-sm">Choose a patient thread from the left pane to view message history and send manual replies.</p>
                </div>
            @endif
        </div>
    </div>
</div>
