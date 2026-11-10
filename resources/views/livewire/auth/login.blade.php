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
            <!-- Demo Quick Access Panel (Doctor Demo Only) -->
            <div class="mb-5 p-4 bg-gradient-to-r from-blue-50/90 via-slate-50 to-orange-50/80 border border-blue-200/90 rounded-2xl text-center shadow-xs">
                <div class="flex items-center justify-center gap-1.5 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6200] animate-pulse"></span>
                    <span class="text-xs font-black uppercase text-[#1e3fae] tracking-wide">Doctor Interactive Demo</span>
                </div>
                <p class="text-[11px] text-slate-600 mb-3 font-medium">
                    Showcase the 1-click clinical studio, voice assistant & digital prescribing
                </p>
                <button
                    type="button"
                    wire:click="loginAsDemo('doctor')"
                    class="w-full bg-[#1e3fae] hover:bg-[#173087] text-white font-black py-2.5 px-4 rounded-xl text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                    title="Launch Doctor Demo"
                >
                    <span>🩺 Launch Doctor Demo (1-Click)</span>
                    <span wire:loading wire:target="loginAsDemo" class="animate-spin">⏳</span>
                </button>
                <div class="mt-2 text-[10px] text-slate-500 font-medium">
                    Demo Account: <span class="font-mono text-slate-700 font-bold">doctor@bikrify.com</span>
                </div>
            </div>

            <div class="relative flex items-center justify-center my-4">
                <div class="border-t border-slate-200 w-full"></div>
                <span class="bg-white px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Or Sign in with Credentials</span>
                <div class="border-t border-slate-200 w-full"></div>
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
