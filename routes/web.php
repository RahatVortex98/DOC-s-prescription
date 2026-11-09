<?php

use App\Http\Controllers\ChamberController;
use App\Http\Controllers\PrescriptionPrintController;
use App\Livewire\AppointmentManager;
use App\Livewire\PrescriptionBuilder;
use App\Livewire\PrescriptionList;
use Illuminate\Support\Facades\Route;

Route::get('/', PrescriptionBuilder::class)->name('prescription.builder');

Route::get('/prescriptions', PrescriptionList::class)->name('prescription.list');

Route::get('/appointments', AppointmentManager::class)->name('appointment.manager');

Route::get('/book/{chamberId?}', AppointmentManager::class)->name('appointment.public_book');

Route::get('/chambers/{chamber}/qr-standee', [ChamberController::class, 'qrStandee'])->name('chamber.qr_standee');

Route::get('/prescriptions/{prescription}/print', [PrescriptionPrintController::class, 'show'])
    ->name('prescription.print');

Route::get('/prescriptions/{prescription}/pdf', [PrescriptionPrintController::class, 'downloadPdf'])
    ->name('prescription.pdf');

Route::get('/prescriptions/{prescription}/investigations', [PrescriptionPrintController::class, 'investigationSlip'])
    ->name('prescription.investigations');
