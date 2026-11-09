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
    @unless(request()->routeIs('appointment.public_book') || request()->routeIs('chamber.qr_standee') || request()->routeIs('prescription.print') || request()->routeIs('prescription.investigations'))
        <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-14">
                <div class="flex items-center gap-6">
                    <a href="{{ route('prescription.builder') }}" class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-cyan-700 text-white font-black flex items-center justify-center text-base shadow-xs">℞</span>
                        <div>
                            <span class="font-black text-cyan-900 tracking-tight text-base">Bikrify Health</span>
                            <span class="hidden sm:inline-block text-[10px] bg-cyan-100 text-cyan-800 font-bold px-1.5 py-0.2 rounded ml-1">Studio</span>
                        </div>
                    </a>
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
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('chamber.qr_standee', 1) }}" target="_blank" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1.5 rounded-md font-semibold border flex items-center gap-1" title="Chamber Door Standee">
                        <span>📲</span> Door QR
                    </a>
                    <a href="{{ route('appointment.public_book', 1) }}" target="_blank" class="text-xs bg-cyan-700 hover:bg-cyan-800 text-white px-2.5 py-1.5 rounded-md font-bold flex items-center gap-1" title="Patient Self Booking">
                        <span>🔗</span> Patient Portal
                    </a>
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
