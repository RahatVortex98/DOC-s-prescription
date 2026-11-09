<?php

use App\Http\Controllers\PrescriptionPrintController;
use App\Livewire\PrescriptionBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/', PrescriptionBuilder::class)->name('prescription.builder');

Route::get('/prescriptions/{prescription}/print', [PrescriptionPrintController::class, 'show'])
    ->name('prescription.print');

Route::get('/prescriptions/{prescription}/pdf', [PrescriptionPrintController::class, 'downloadPdf'])
    ->name('prescription.pdf');
