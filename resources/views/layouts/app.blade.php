<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Bikrify Prescription Studio' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased min-h-screen">
    @unless(request()->routeIs('login') || request()->routeIs('appointment.public_book') || request()->routeIs('chamber.qr_standee') || request()->routeIs('prescription.print') || request()->routeIs('prescription.investigations') || request()->routeIs('prescription.pdf') || request()->routeIs('prescription.public_share'))
        <nav x-data="{ mobileMenuOpen: false }" class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-14">
                    <!-- Brand Logo -->
                    <div class="flex items-center gap-6">
                        <a href="{{ auth()->check() && auth()->user()->isSuperAdmin() ? route('admin.tenants') : route('prescription.builder') }}" class="flex items-center gap-2.5">
                            <img src="{{ asset('images/bikrify-logo.png') }}" alt="Bikrify" class="w-8 h-8 rounded-lg shadow-xs">
                            <div class="flex items-center">
                                <span class="font-black text-[#1e3fae] text-lg tracking-tight">Bikrify</span>
                                <span class="font-black text-[#ff6200] text-lg ml-0.5">Health</span>
                                <span class="hidden sm:inline-block text-[10px] bg-blue-100 text-[#1e3fae] font-extrabold px-1.5 py-0.5 rounded ml-1.5">Studio</span>
                            </div>
                        </a>

                        <!-- Desktop Navigation -->
                        @auth
                            @if(auth()->user()->isSuperAdmin())
                                <div class="hidden md:flex items-center gap-1">
                                    <a href="{{ route('admin.tenants') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold @if(request()->routeIs('admin.*')) bg-blue-50 text-[#1e3fae] border border-blue-200 @else text-slate-600 hover:text-[#1e3fae] hover:bg-slate-50 @endif transition">
                                        🏢 Tenant Management
                                    </a>
                                </div>
                            @else
                                <div class="hidden md:flex items-center gap-1">
                                    <a href="{{ route('prescription.builder') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold @if(request()->routeIs('prescription.builder')) bg-blue-50 text-[#1e3fae] border border-blue-200 @else text-slate-600 hover:text-[#1e3fae] hover:bg-slate-50 @endif transition">
                                        Prescription Studio
                                    </a>
                                    <a href="{{ route('appointment.manager') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold @if(request()->routeIs('appointment.manager')) bg-blue-50 text-[#1e3fae] border border-blue-200 @else text-slate-600 hover:text-[#1e3fae] hover:bg-slate-50 @endif transition">
                                        Queue & Appointments
                                    </a>
                                    <a href="{{ route('prescription.list') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold @if(request()->routeIs('prescription.list')) bg-blue-50 text-[#1e3fae] border border-blue-200 @else text-slate-600 hover:text-[#1e3fae] hover:bg-slate-50 @endif transition">
                                        Prescription Records
                                    </a>
                                    <a href="{{ route('doctor.dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold @if(request()->routeIs('doctor.dashboard')) bg-blue-50 text-[#1e3fae] border border-blue-200 @else text-slate-600 hover:text-[#1e3fae] hover:bg-slate-50 @endif transition">
                                        Practice Analytics
                                    </a>
                                    <a href="{{ route('audit.logs') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold @if(request()->routeIs('audit.logs')) bg-blue-50 text-[#1e3fae] border border-blue-200 @else text-slate-600 hover:text-[#1e3fae] hover:bg-slate-50 @endif transition">
                                        Audit Logs
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>

                    <!-- Right Controls (Desktop & Mobile trigger) -->
                    <div class="flex items-center gap-2">
                        @auth
                            <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200 text-xs">
                                <span class="font-bold text-slate-700">{{ auth()->user()->name }}</span>
                                <span class="bg-blue-50 text-[#1e3fae] border border-blue-200 text-[10px] font-black uppercase px-1.5 py-0.5 rounded">
                                    {{ auth()->user()->role }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                                @csrf
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 rounded hover:bg-rose-50 transition cursor-pointer">
                                    Sign out
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-xs bg-[#1e3fae] hover:bg-[#173087] text-white px-3 py-1.5 rounded-lg font-bold transition">
                                Sign in
                            </a>
                        @endauth

                        <!-- Mobile Hamburger Button -->
                        @auth
                            <button
                                @click="mobileMenuOpen = !mobileMenuOpen"
                                type="button"
                                class="md:hidden p-2 rounded-lg text-slate-600 hover:text-[#1e3fae] hover:bg-blue-50 focus:outline-none transition"
                                aria-label="Toggle navigation menu"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Mobile Dropdown Navigation Menu -->
            @auth
                <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-slate-200 bg-white px-4 pt-2 pb-4 space-y-1 shadow-lg">
                    <div class="py-2 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-xs text-slate-800">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-500">{{ auth()->user()->email }}</div>
                        </div>
                        <span class="bg-blue-50 text-[#1e3fae] border border-blue-200 text-[10px] font-black uppercase px-2 py-0.5 rounded">
                            {{ auth()->user()->role }}
                        </span>
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.tenants') }}" class="block px-3 py-2 rounded-lg text-xs font-bold @if(request()->routeIs('admin.*')) bg-blue-50 text-[#1e3fae] @else text-slate-700 hover:bg-slate-50 @endif">
                            🏢 Tenant Management
                        </a>
                    @else
                        <a href="{{ route('prescription.builder') }}" class="block px-3 py-2 rounded-lg text-xs font-bold @if(request()->routeIs('prescription.builder')) bg-blue-50 text-[#1e3fae] @else text-slate-700 hover:bg-slate-50 @endif">
                            🩺 Prescription Studio
                        </a>
                        <a href="{{ route('appointment.manager') }}" class="block px-3 py-2 rounded-lg text-xs font-bold @if(request()->routeIs('appointment.manager')) bg-blue-50 text-[#1e3fae] @else text-slate-700 hover:bg-slate-50 @endif">
                            👥 Queue & Appointments
                        </a>
                        <a href="{{ route('prescription.list') }}" class="block px-3 py-2 rounded-lg text-xs font-bold @if(request()->routeIs('prescription.list')) bg-blue-50 text-[#1e3fae] @else text-slate-700 hover:bg-slate-50 @endif">
                            📋 Prescription Records
                        </a>
                        <a href="{{ route('doctor.dashboard') }}" class="block px-3 py-2 rounded-lg text-xs font-bold @if(request()->routeIs('doctor.dashboard')) bg-blue-50 text-[#1e3fae] @else text-slate-700 hover:bg-slate-50 @endif">
                            📊 Practice Analytics
                        </a>
                        <a href="{{ route('audit.logs') }}" class="block px-3 py-2 rounded-lg text-xs font-bold @if(request()->routeIs('audit.logs')) bg-blue-50 text-[#1e3fae] @else text-slate-700 hover:bg-slate-50 @endif">
                            🛡️ Audit Logs
                        </a>
                    @endif

                    <div class="pt-2 border-t border-slate-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                🚪 Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </nav>
    @endunless

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
