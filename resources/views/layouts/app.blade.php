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
    @unless(request()->routeIs('login') || request()->routeIs('appointment.public_book') || request()->routeIs('chamber.qr_standee') || request()->routeIs('prescription.print') || request()->routeIs('prescription.investigations') || request()->routeIs('prescription.pdf'))
        <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-14">
                <div class="flex items-center gap-6">
                    <a href="{{ auth()->check() && auth()->user()->isSuperAdmin() ? route('admin.tenants') : route('prescription.builder') }}" class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-cyan-700 text-white font-black flex items-center justify-center text-base shadow-xs">℞</span>
                        <div>
                            <span class="font-black text-cyan-900 tracking-tight text-base">Bikrify Health</span>
                            <span class="hidden sm:inline-block text-[10px] bg-cyan-100 text-cyan-800 font-bold px-1.5 py-0.2 rounded ml-1">Studio</span>
                        </div>
                    </a>

                    @auth
                        @if(auth()->user()->isSuperAdmin())
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.tenants') }}" class="px-3 py-1.5 rounded-md text-xs font-bold @if(request()->routeIs('admin.*')) bg-purple-50 text-purple-800 border border-purple-200 @else text-slate-600 hover:text-purple-800 hover:bg-slate-50 @endif">
                                    🏢 Tenant Management
                                </a>
                            </div>
                        @else
                            <div class="hidden sm:flex items-center gap-1">
                                <a href="{{ route('prescription.builder') }}" class="px-3 py-1.5 rounded-md text-xs font-bold @if(request()->routeIs('prescription.builder')) bg-cyan-50 text-cyan-800 border border-cyan-200 @else text-slate-600 hover:text-cyan-800 hover:bg-slate-50 @endif">
                                    Prescription Studio
                                </a>
                                <a href="{{ route('appointment.manager') }}" class="px-3 py-1.5 rounded-md text-xs font-bold @if(request()->routeIs('appointment.manager')) bg-cyan-50 text-cyan-800 border border-cyan-200 @else text-slate-600 hover:text-cyan-800 hover:bg-slate-50 @endif">
                                    Queue & Appointments
                                </a>
                                <a href="{{ route('prescription.list') }}" class="px-3 py-1.5 rounded-md text-xs font-bold @if(request()->routeIs('prescription.list')) bg-cyan-50 text-cyan-800 border border-cyan-200 @else text-slate-600 hover:text-cyan-800 hover:bg-slate-50 @endif">
                                    Prescription Records
                                </a>
                                <a href="{{ route('doctor.dashboard') }}" class="px-3 py-1.5 rounded-md text-xs font-bold @if(request()->routeIs('doctor.dashboard')) bg-cyan-50 text-cyan-800 border border-cyan-200 @else text-slate-600 hover:text-cyan-800 hover:bg-slate-50 @endif">
                                    Practice Analytics
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        @if(auth()->user()->tenant)
                            <a href="{{ route('appointment.public_book', auth()->user()->tenant->slug) }}" target="_blank" class="text-xs bg-cyan-700 hover:bg-cyan-800 text-white px-2.5 py-1.5 rounded-md font-bold flex items-center gap-1" title="Public Patient Booking Page">
                                <span>🔗</span> Booking Page
                            </a>
                        @endif

                        <div class="flex items-center gap-2 pl-2 border-l border-slate-200 text-xs">
                            <span class="font-semibold text-slate-700 hidden md:inline">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold px-2 py-1 rounded hover:bg-rose-50 transition">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-xs bg-cyan-700 hover:bg-cyan-800 text-white px-3 py-1.5 rounded-md font-bold">
                            Sign in
                        </a>
                    @endauth
                </div>
            </div>
        </nav>
    @endunless

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
