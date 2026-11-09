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
        $prescription->load(['patient', 'items']);

        return view('prescriptions.print', compact('prescription'));
    }

    public function downloadPdf(Prescription $prescription): Response
    {
        $prescription->load(['patient', 'items']);

        $pdf = Pdf::loadView('prescriptions.print', compact('prescription'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Prescription-{$prescription->prescription_no}.pdf");
    }
}
