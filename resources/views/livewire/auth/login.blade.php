<div class="min-h-[85vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-800 to-cyan-900 px-8 py-6 text-white text-center">
            <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-2xl mx-auto mb-2 font-black border border-white/20">
                ℞
            </div>
            <h1 class="text-xl font-bold tracking-tight">Bikrify Prescription Studio</h1>
            <p class="text-cyan-200 text-xs mt-1">Multi-Tenant Clinical SaaS Platform</p>
        </div>

        <form wire:submit.prevent="login" class="p-8 space-y-5">
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
                    class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:border-cyan-600 transition"
                >
                @error('email') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Password
                </label>
                <input
                    type="password"
                    id="password"
                    wire:model="password"
                    required
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:border-cyan-600 transition"
                >
                @error('password') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600">
                    <span class="text-xs text-slate-600">Remember me</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-cyan-700 hover:bg-cyan-800 text-white font-bold py-2.5 px-4 rounded-lg text-sm shadow-md transition flex items-center justify-center gap-2"
            >
                <span>Sign In to Studio</span>
                <span wire:loading wire:target="login" class="animate-spin">⏳</span>
            </button>

            <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                Clinic accounts are provisioned exclusively by Bikrify.
            </div>
        </form>
    </div>
</div>
