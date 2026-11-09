<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - {{ $prescription->prescription_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1f2937; margin: 0; padding: 0; font-size: 13px; line-height: 1.4; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0891b2; padding-bottom: 12px; }
        .doc-name { font-size: 18px; font-weight: bold; color: #0e7490; }
        .doc-qual { font-size: 12px; color: #4b5563; }
        .patient-strip { background: #f0fdfa; border: 1px solid #ccfbf1; padding: 8px 12px; margin: 15px 0; display: flex; justify-content: space-between; font-weight: 500; font-size: 12px; }
        .container { display: flex; min-height: 700px; }
        .left-col { width: 35%; border-right: 1.5px solid #e5e7eb; padding-right: 15px; }
        .right-col { width: 65%; padding-left: 20px; }
        .section-title { font-weight: bold; text-transform: uppercase; font-size: 11px; color: #0891b2; margin-top: 15px; margin-bottom: 5px; }
        .rx-symbol { font-size: 32px; font-weight: bold; font-family: serif; color: #0891b2; line-height: 1; margin-bottom: 15px; }
        .medicine-item { margin-bottom: 14px; }
        .med-title { font-weight: bold; font-size: 13px; }
        .med-detail { font-size: 12px; color: #4b5563; padding-left: 15px; }
        .footer { margin-top: 40px; border-top: 1px dashed #cbd5e1; padding-top: 10px; display: flex; justify-content: space-between; font-size: 11px; color: #64748b; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background:#0891b2; padding:10px 20px; text-align:right; margin-bottom:20px;">
        <button onclick="window.print()" style="padding:6px 14px; background:#fff; color:#0891b2; font-weight:bold; border:none; border-radius:4px; cursor:pointer;">Print Now</button>
        <a href="{{ route('prescription.pdf', $prescription->id) }}" style="padding:6px 14px; background:#0e7490; color:#fff; font-weight:bold; text-decoration:none; border-radius:4px; margin-left:10px;">Download PDF</a>
    </div>

    <!-- Chamber & Doctor Header -->
    <div class="header">
        <div>
            <div class="doc-name">{{ $prescription->doctor_name }}</div>
            <div class="doc-qual">{{ $prescription->doctor_qualification }}</div>
            <div style="font-size: 11px; color: #6b7280;">Bikrify Verified Medical Practitioner</div>
        </div>
        <div style="text-align: right;">
            <div style="font-weight: bold; font-size: 15px; color: #0891b2;">{{ $prescription->chamber_name }}</div>
            <div style="font-size: 11px; color: #6b7280;">Rx No: {{ $prescription->prescription_no }}</div>
            <div style="font-size: 11px; color: #6b7280;">Date: {{ \Carbon\Carbon::parse($prescription->prescription_date)->format('d M, Y') }}</div>
        </div>
    </div>

    <!-- Patient Info Bar -->
    <div class="patient-strip">
        <div><strong>Patient:</strong> {{ $prescription->patient->name }}</div>
        <div><strong>Age:</strong> {{ $prescription->patient->age }} Y</div>
        <div><strong>Gender:</strong> {{ $prescription->patient->gender }}</div>
        <div><strong>ID:</strong> {{ $prescription->patient->patient_id }}</div>
    </div>

    <!-- Main Body -->
    <div class="container">
        <!-- Left Clinical Details -->
        <div class="left-col">
            @if($prescription->chief_complaints)
                <div class="section-title">Chief Complaints</div>
                <div style="white-space: pre-line;">{{ $prescription->chief_complaints }}</div>
            @endif

            @if($prescription->clinical_findings)
                <div class="section-title">On Examination (O/E)</div>
                <div style="white-space: pre-line;">{{ $prescription->clinical_findings }}</div>
            @endif

            @if($prescription->diagnosis)
                <div class="section-title">Diagnosis</div>
                <div style="white-space: pre-line;"><strong>{{ $prescription->diagnosis }}</strong></div>
            @endif

            @if($prescription->investigations)
                <div class="section-title">Investigations</div>
                <div style="white-space: pre-line;">{{ $prescription->investigations }}</div>
            @endif
        </div>

        <!-- Right Rx Section -->
        <div class="right-col">
            <div class="rx-symbol">℞</div>
            @foreach($prescription->items as $index => $item)
                <div class="medicine-item">
                    <div class="med-title">
                        {{ $index + 1 }}. {{ $item->type }} {{ $item->medicine_name }} {{ $item->strength }}
                        @if($item->generic_name)
                            <span style="font-weight: normal; color:#6b7280; font-size:11px;">({{ $item->generic_name }})</span>
                        @endif
                    </div>
                    <div class="med-detail">
                        {{ $item->dosage }} &nbsp; — &nbsp; {{ $item->duration }} &nbsp; ({{ $item->instructions }})
                    </div>
                </div>
            @endforeach

            @if($prescription->advice)
                <div class="section-title" style="margin-top: 30px;">Advice</div>
                <div style="white-space: pre-line;">{{ $prescription->advice }}</div>
            @endif
        </div>
    </div>

    <!-- Sign-off Footer -->
    <div class="footer">
        <div>Powered by <strong>Bikrify Digital Health Platform</strong></div>
        <div style="text-align: right;">Doctor's Signature: _________________________</div>
    </div>
</body>
</html>
