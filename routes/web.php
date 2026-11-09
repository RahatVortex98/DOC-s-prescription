<?php

use App\Http\Controllers\PrescriptionPrintController;
use App\Livewire\AppointmentManager;
use App\Livewire\PrescriptionBuilder;
use App\Livewire\PrescriptionList;
use Illuminate\Support\Facades\Route;

Route::get('/', PrescriptionBuilder::class)->name('prescription.builder');

Route::get('/prescriptions', PrescriptionList::class)->name('prescription.list');

Route::get('/appointments', AppointmentManager::class)->name('appointment.manager');

Route::get('/book/{chamberId?}', function (?int $chamberId = null) {
    return app(AppointmentManager::class, ['chamberId' => $chamberId, 'portal' => true]);
})->name('appointment.public_book');

Route::get('/prescriptions/{prescription}/print', [PrescriptionPrintController::class, 'show'])
    ->name('prescription.print');

Route::get('/prescriptions/{prescription}/pdf', [PrescriptionPrintController::class, 'downloadPdf'])
    ->name('prescription.pdf');
