<div class="min-h-screen bg-slate-50 flex flex-col justify-center py-8 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Bikrify Brand Logo -->
        <div class="flex items-center justify-center gap-3 mb-3">
            <img src="{{ asset('images/bikrify-logo.png') }}" alt="Bikrify" class="w-12 h-12 rounded-xl shadow-md">
            <div class="text-left">
                <span class="text-2xl font-black text-[#1e3fae] tracking-tight">Bikrify</span>
                <span class="text-2xl font-black text-[#ff6200] ml-1">Health</span>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Prescription Studio</div>
            </div>
        </div>
        <p class="text-xs text-slate-600 font-medium">Smart software to run and grow your clinical practice</p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-7 px-5 sm:px-8 shadow-xl rounded-2xl border border-slate-200">
            <!-- Demo Quick Access Panel -->
            <div class="mb-6 p-3.5 bg-gradient-to-r from-blue-50 to-orange-50 border border-blue-200/80 rounded-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-black uppercase text-[#1e3fae] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#ff6200] animate-pulse"></span>
                        ⚡ 1-Click Demo Login
                    </span>
                    <span class="text-[10px] text-slate-500 font-semibold">Ready to Test</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        type="button"
                        wire:click="loginAsDemo('doctor')"
                        class="bg-[#1e3fae] hover:bg-[#173087] text-white text-[11px] font-bold py-2 px-2 rounded-lg transition shadow-xs flex flex-col items-center justify-center cursor-pointer"
                        title="Sign in as Dr. Mahbubur Rahman"
                    >
                        <span>🩺 Doctor</span>
                        <span class="text-[9px] opacity-80 font-normal">Full Studio</span>
                    </button>
                    <button
                        type="button"
                        wire:click="loginAsDemo('assistant')"
                        class="bg-slate-700 hover:bg-slate-800 text-white text-[11px] font-bold py-2 px-2 rounded-lg transition shadow-xs flex flex-col items-center justify-center cursor-pointer"
                        title="Sign in as Clinic Receptionist"
                    >
                        <span>📋 Assistant</span>
                        <span class="text-[9px] opacity-80 font-normal">Queue Desk</span>
                    </button>
                    <button
                        type="button"
                        wire:click="loginAsDemo('super_admin')"
                        class="bg-[#ff6200] hover:bg-[#ea580c] text-white text-[11px] font-bold py-2 px-2 rounded-lg transition shadow-xs flex flex-col items-center justify-center cursor-pointer"
                        title="Sign in as Bikrify Super Admin"
                    >
                        <span>🛡️ Admin</span>
                        <span class="text-[9px] opacity-80 font-normal">Governance</span>
                    </button>
                </div>
                <div class="mt-2 text-[10px] text-slate-600 text-center flex items-center justify-center gap-2">
                    <span>Default Password: <code class="font-bold text-[#1e3fae]">password</code></span>
                </div>
            </div>

            <!-- Standard Login Form -->
            <form wire:submit.prevent="login" class="space-y-4">
                @if ($errors->has('email'))
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-700 text-xs font-semibold">
                        {{ $errors->first('email') }}
                    </div>
                @endif

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Doctor / Staff Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        required
                        autofocus
                        placeholder="doctor@bikrify.com"
                        class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1e3fae] focus:border-[#1e3fae] transition"
                    >
                    @error('email') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Password
                        </label>
                    </div>
                    <input
                        type="password"
                        id="password"
                        wire:model="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1e3fae] focus:border-[#1e3fae] transition"
                    >
                    @error('password') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-[#1e3fae] focus:ring-[#1e3fae]">
                        <span class="text-xs text-slate-600">Remember me</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full bg-[#1e3fae] hover:bg-[#173087] text-white font-bold py-2.5 px-4 rounded-lg text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span>Sign In to Studio</span>
                    <span wire:loading wire:target="login,loginAsDemo" class="animate-spin">⏳</span>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Bikrify Digital Health Platform &copy; {{ date('Y') }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>
