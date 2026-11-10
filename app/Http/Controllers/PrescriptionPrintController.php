<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class PrescriptionPrintController extends Controller
{
    public function show(Prescription $prescription): View
    {
        $prescription->load(['patient', 'items', 'chamber', 'tenant']);

        return view('prescriptions.print', compact('prescription'));
    }

    public function downloadPdf(Prescription $prescription): Response
    {
        $prescription->load(['patient', 'items', 'chamber', 'tenant']);

        $pdf = Pdf::loadView('prescriptions.print', compact('prescription'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Prescription-{$prescription->prescription_no}.pdf");
    }

    public function investigationSlip(Prescription $prescription): View
    {
        $prescription->load(['patient', 'chamber', 'tenant']);

        return view('prescriptions.investigations', compact('prescription'));
    }
}
