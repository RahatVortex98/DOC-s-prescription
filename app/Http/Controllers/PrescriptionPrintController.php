<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class PrescriptionPrintController extends Controller
{
    public function show(Prescription $prescription): View
    {
        $prescription->load(['patient', 'items', 'chamber', 'tenant']);

        AuditLog::record('prescription.printed', $prescription, [
            'rx_no' => $prescription->prescription_no,
        ]);

        return view('prescriptions.print', compact('prescription'));
    }

    public function downloadPdf(Prescription $prescription): Response
    {
        $prescription->load(['patient', 'items', 'chamber', 'tenant']);

        AuditLog::record('prescription.pdf_downloaded', $prescription, [
            'rx_no' => $prescription->prescription_no,
        ]);

        $pdf = Pdf::loadView('prescriptions.print', compact('prescription'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Prescription-{$prescription->prescription_no}.pdf");
    }

    public function investigationSlip(Prescription $prescription): View
    {
        $prescription->load(['patient', 'chamber', 'tenant']);

        return view('prescriptions.investigations', compact('prescription'));
    }

    public function publicShare(string $public_id): View
    {
        $prescription = Prescription::withoutGlobalScope('tenant')
            ->with([
                'patient' => fn ($q) => $q->withoutGlobalScope('tenant'),
                'chamber' => fn ($q) => $q->withoutGlobalScope('tenant'),
                'tenant',
                'items',
            ])
            ->where('public_id', $public_id)
            ->firstOrFail();

        AuditLog::record('prescription.shared_viewed', $prescription, [
            'rx_no' => $prescription->prescription_no,
        ]);

        return view('prescriptions.print', compact('prescription'));
    }
}
