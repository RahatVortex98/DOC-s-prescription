<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-2xl shadow-xs">
                💬
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    WhatsApp Business Platform Integration
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Meta Cloud API
                    </span>
                </h1>
                <p class="text-xs text-slate-500">
                    Connect your clinic's own WhatsApp Business number for automated patient booking, confirmation messages, and live chat.
                </p>
            </div>
        </div>

        @if($integration && $integration->isConnected())
            <button
                type="button"
                wire:click="openConnectModal"
                class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
                ⚙️ Update Credentials
            </button>
        @else
            <button
                type="button"
                wire:click="openConnectModal"
                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition flex items-center gap-2 cursor-pointer shadow-md"
            >
                <span>💬 Connect WhatsApp Business</span>
            </button>
        @endif
    </div>

    <!-- Notification Alerts -->
    @if($feedback_message)
        <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <span>{{ $feedback_message }}</span>
            <button wire:click="$set('feedback_message', null)" class="text-emerald-500 hover:text-emerald-800 font-bold">✕</button>
        </div>
    @endif

    @if($error_message)
        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <span>{{ $error_message }}</span>
            <button wire:click="$set('error_message', null)" class="text-rose-500 hover:text-rose-800 font-bold">✕</button>
        </div>
    @endif

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Status & Account Details (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Connection Status Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm font-bold text-slate-800">Connection Status</span>
                    </div>

                    @if($integration && $integration->isConnected())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Connected & Live
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Disconnected
                        </span>
                    @endif
                </div>

                @if($integration && $integration->isConnected())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-6">
                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">WhatsApp Business Number</span>
                            <span class="text-sm font-black text-slate-900 font-mono">{{ $integration->display_phone_number }}</span>
                        </div>

                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Phone Number ID</span>
                            <span class="text-xs font-bold text-slate-700 font-mono">{{ $integration->phone_number_id }}</span>
                        </div>

                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">WhatsApp Business Account ID (WABA)</span>
                            <span class="text-xs font-bold text-slate-700 font-mono">{{ $integration->waba_id }}</span>
                        </div>

                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Connected Since</span>
                            <span class="text-xs font-bold text-slate-700">{{ $integration->connected_at ? $integration->connected_at->format('d M Y, h:i A') : 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Test Ping Section -->
                    <div class="p-4 bg-emerald-50/60 border border-emerald-100 rounded-xl mb-4">
                        <h4 class="text-xs font-bold text-emerald-900 mb-1 flex items-center gap-1.5">
                            <span>📡</span> Verify Live Outbound Connection
                        </h4>
                        <p class="text-[11px] text-emerald-700 mb-3">
                            Send a real-time WhatsApp verification ping to your mobile phone to test Meta Cloud API message delivery.
                        </p>

                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                wire:model="test_phone_number"
                                placeholder="e.g. 01711223344 or 8801711223344"
                                class="flex-1 px-3 py-2 text-xs border border-emerald-300 rounded-lg bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                            <button
                                type="button"
                                wire:click="sendTestPing"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1 shadow-xs"
                            >
                                <span>Send Test</span>
                                <span wire:loading wire:target="sendTestPing" class="animate-spin">⏳</span>
                            </button>
                        </div>
                        @error('test_phone_number')
                            <span class="text-[10px] text-rose-600 font-bold mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">Tokens are protected with 256-bit AES encryption at rest.</span>
                        <button
                            type="button"
                            wire:click="disconnect"
                            wire:confirm="Are you sure you want to disconnect this WhatsApp Business number? Inbound automated bookings will be paused."
                            class="text-xs text-rose-600 hover:text-rose-800 font-bold hover:underline cursor-pointer"
                        >
                            Disconnect Number
                        </button>
                    </div>
                @else
                    <div class="text-center py-10 px-4">
                        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-3">
                            📵
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 mb-1">No WhatsApp Business Number Connected</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mb-5">
                            Connect your clinic's WhatsApp number to enable patients to book appointments, receive serial confirmation messages, and communicate with your front desk.
                        </p>
                        <button
                            type="button"
                            wire:click="openConnectModal"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition inline-flex items-center gap-2 cursor-pointer shadow-md"
                        >
                            <span>💬 Connect WhatsApp Business Account</span>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Features Enabled by WhatsApp Integration -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider mb-4">
                    Automated Capabilities Enabled Once Connected
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3.5 border border-slate-100 rounded-xl bg-slate-50/60">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-base">🤖</span>
                            <span class="text-xs font-bold text-slate-900">Deterministic Booking Chatbot</span>
                        </div>
                        <p class="text-[11px] text-slate-600">
                            Patients can text "Book Appointment" to choose chambers, dates, and receive confirmed serial numbers instantly without human staff delay.
                        </p>
                    </div>

                    <div class="p-3.5 border border-slate-100 rounded-xl bg-slate-50/60">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-base">🎟️</span>
                            <span class="text-xs font-bold text-slate-900">Unified Token Queue</span>
                        </div>
                        <p class="text-[11px] text-slate-600">
                            WhatsApp bookings automatically synchronize with your receptionist queue and in-clinic patient monitor.
                        </p>
                    </div>

                    <div class="p-3.5 border border-slate-100 rounded-xl bg-slate-50/60">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-base">💬</span>
                            <span class="text-xs font-bold text-slate-900">Front-Desk Clinical Inbox</span>
                        </div>
                        <p class="text-[11px] text-slate-600">
                            Assistants can monitor chat threads, pause the automated bot with one click, and respond manually to complex patient queries.
                        </p>
                    </div>

                    <div class="p-3.5 border border-slate-100 rounded-xl bg-slate-50/60">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-base">🛡️</span>
                            <span class="text-xs font-bold text-slate-900">Strict Clinic Isolation</span>
                        </div>
                        <p class="text-[11px] text-slate-600">
                            Your WhatsApp phone number ID and messages are strictly scoped to your clinic tenant. No other doctor can view your patient chats.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Technical Setup & Meta Webhook Guide (1 Col) -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-slate-900 to-[#173087] text-white rounded-2xl shadow-md p-5 border border-blue-900/40">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-lg">⚙️</span>
                    <h3 class="text-xs font-black uppercase tracking-wider">Meta Cloud API Configuration</h3>
                </div>
                <p class="text-xs text-blue-100 mb-4">
                    To link a WhatsApp Business number, you need a Meta Developer App configured with the WhatsApp product.
                </p>

                <div class="space-y-3 text-[11px]">
                    <div class="p-3 bg-white/10 rounded-xl border border-white/10">
                        <span class="font-bold text-white block mb-0.5">Webhook Callback URL:</span>
                        <code class="text-emerald-300 font-mono text-[10px] break-all block">{{ url('/webhooks/whatsapp') }}</code>
                    </div>

                    <div class="p-3 bg-white/10 rounded-xl border border-white/10">
                        <span class="font-bold text-white block mb-0.5">Webhook Verify Token:</span>
                        <code class="text-emerald-300 font-mono text-[10px] break-all block">{{ config('services.whatsapp.verify_token') ?: 'Set in .env (WHATSAPP_VERIFY_TOKEN)' }}</code>
                    </div>

                    <div class="p-3 bg-white/10 rounded-xl border border-white/10">
                        <span class="font-bold text-white block mb-0.5">Webhook Field Subscriptions:</span>
                        <span class="text-blue-100 font-medium">Subscribe to <code class="text-white font-bold">messages</code> and <code class="text-white font-bold">message_status</code>.</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h4 class="text-xs font-bold text-slate-800 mb-2 flex items-center gap-1.5">
                    <span>💡</span> Meta Embedded Signup Guide
                </h4>
                <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1.5 leading-relaxed">
                    <li>Create or use your Meta Business Account in <strong>business.facebook.com</strong>.</li>
                    <li>Add a dedicated phone number with SMS/Call verification capability.</li>
                    <li>Generate a System User Permanent Access Token with <code>whatsapp_business_messaging</code> permissions.</li>
                    <li>Paste the WABA ID, Phone Number ID, and Access Token into the modal.</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Connect / Update Credentials Modal -->
    @if($show_connect_modal)
        <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200" @click.away="$wire.closeConnectModal()">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">💬</span>
                        <div>
                            <h3 class="font-black text-base text-slate-900">Connect WhatsApp Business</h3>
                            <p class="text-[11px] text-slate-500">Enter your Meta WhatsApp Cloud API credentials</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeConnectModal" class="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
                </div>

                <form wire:submit.prevent="saveCredentials" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp Business Account ID (WABA ID) *</label>
                        <input
                            type="text"
                            wire:model="waba_id"
                            placeholder="e.g. 102938475610293"
                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-[#1e3fae] focus:outline-none"
                        >
                        @error('waba_id') <span class="text-[10px] text-rose-600 font-bold mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number ID *</label>
                        <input
                            type="text"
                            wire:model="phone_number_id"
                            placeholder="e.g. 564738291029384"
                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-[#1e3fae] focus:outline-none"
                        >
                        @error('phone_number_id') <span class="text-[10px] text-rose-600 font-bold mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Phone Number *</label>
                        <input
                            type="text"
                            wire:model="display_phone_number"
                            placeholder="e.g. +880 1711-223344"
                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#1e3fae] focus:outline-none"
                        >
                        @error('display_phone_number') <span class="text-[10px] text-rose-600 font-bold mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Permanent Access Token *
                            <span class="text-[10px] font-normal text-slate-500">(System User or Cloud API Token)</span>
                        </label>
                        <textarea
                            wire:model="access_token"
                            rows="3"
                            placeholder="EAAG..."
                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-[#1e3fae] focus:outline-none"
                        ></textarea>
                        @error('access_token') <span class="text-[10px] text-rose-600 font-bold mt-0.5 block">{{ $message }}</span> @enderror
                        <p class="text-[10px] text-slate-400 mt-1">
                            🔒 Encrypted with AES-256 before saving to the database. Never exposed in plaintext.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            wire:click="closeConnectModal"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black transition flex items-center gap-1.5 cursor-pointer shadow-md"
                        >
                            <span>Save & Connect</span>
                            <span wire:loading wire:target="saveCredentials" class="animate-spin">⏳</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
