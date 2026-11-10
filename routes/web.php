<?php

use App\Http\Controllers\ChamberController;
use App\Http\Controllers\PrescriptionPrintController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Livewire\Admin\TenantManager;
use App\Livewire\AppointmentManager;
use App\Livewire\AuditLogViewer;
use App\Livewire\Auth\Login;
use App\Livewire\DoctorDashboard;
use App\Livewire\PrescriptionBuilder;
use App\Livewire\PrescriptionList;
use App\Livewire\WhatsApp\WhatsAppInbox;
use App\Livewire\WhatsApp\WhatsAppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Intelligent Root Redirection
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->isSuperAdmin()) {
            return redirect()->route('admin.tenants');
        }

        return redirect()->route('prescription.builder');
    }

    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Super-Admin Area (role super_admin only)
Route::middleware(['auth', 'role.super_admin'])->prefix('admin')->group(function () {
    Route::get('/tenants', TenantManager::class)->name('admin.tenants');
});

// Authenticated Doctor/Staff App Routes
Route::middleware(['auth', 'tenant.active'])->prefix('app')->group(function () {
    Route::get('/', PrescriptionBuilder::class)->name('prescription.builder');

    Route::get('/dashboard', DoctorDashboard::class)->name('doctor.dashboard');

    Route::get('/prescriptions', PrescriptionList::class)->name('prescription.list');

    Route::get('/appointments', AppointmentManager::class)->name('appointment.manager');

    Route::get('/whatsapp/inbox', WhatsAppInbox::class)->name('whatsapp.inbox');

    Route::get('/chambers/{chamber}/qr-standee', [ChamberController::class, 'qrStandee'])->name('chamber.qr_standee');

    Route::get('/audit-logs', AuditLogViewer::class)->name('audit.logs');

    Route::get('/settings/whatsapp', WhatsAppSettings::class)->name('whatsapp.settings');

    Route::get('/prescriptions/{prescription}/print', [PrescriptionPrintController::class, 'show'])
        ->name('prescription.print');

    Route::get('/prescriptions/{prescription}/pdf', [PrescriptionPrintController::class, 'downloadPdf'])
        ->name('prescription.pdf');

    Route::get('/prescriptions/{prescription}/investigations', [PrescriptionPrintController::class, 'investigationSlip'])
        ->name('prescription.investigations');
});

// Signed Expiring Public Prescription Share Route (valid 7 days, ULID based)
Route::get('/rx/{public_id}', [PrescriptionPrintController::class, 'publicShare'])
    ->name('prescription.public_share')
    ->middleware('signed');

// Centralized Meta WhatsApp Cloud API Webhooks
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])
    ->name('webhooks.whatsapp.verify');

Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle'])
    ->name('webhooks.whatsapp.handle');

// Public Tenant Patient Booking Route (must be registered last)
Route::get('/{tenant:slug}', AppointmentManager::class)
    ->where('tenant', '^(?!app$|login$|logout$|livewire$|admin$|api$|up$|build$|storage$|book$|rx$|audit-logs$|webhooks$|webhook$)[a-z0-9._-]+$')
    ->middleware('throttle:30,1')
    ->name('appointment.public_book');
