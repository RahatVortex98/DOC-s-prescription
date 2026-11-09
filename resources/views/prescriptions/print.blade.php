<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - {{ $prescription->prescription_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1f2937; margin: 0; padding: 0; font-size: 13px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        .header-table { border-bottom: 2px solid #0891b2; padding-bottom: 10px; margin-bottom: 10px; }
        .doc-name { font-size: 18px; font-weight: bold; color: #0e7490; }
        .doc-qual { font-size: 12px; color: #4b5563; margin-top: 2px; }
        .doc-desig { font-size: 11px; color: #0891b2; font-weight: 600; margin-top: 2px; }
        .chamber-title { font-weight: bold; font-size: 15px; color: #0891b2; }
        .chamber-info { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .patient-strip { background: #f0fdfa; border: 1px solid #ccfbf1; padding: 8px 12px; margin: 10px 0 15px 0; }
        .patient-cell { font-size: 12px; color: #1e293b; }
        .body-table { min-height: 650px; }
        .left-col { width: 35%; border-right: 1.5px solid #e2e8f0; padding-right: 15px; vertical-align: top; }
        .right-col { width: 65%; padding-left: 20px; vertical-align: top; }
        .section-title { font-weight: bold; text-transform: uppercase; font-size: 11px; color: #0891b2; margin-top: 15px; margin-bottom: 5px; }
        .rx-symbol { font-size: 32px; font-weight: bold; font-family: serif; color: #0891b2; line-height: 1; margin-bottom: 15px; }
        .medicine-item { margin-bottom: 12px; }
        .med-title { font-weight: bold; font-size: 13px; }
        .med-detail { font-size: 12px; color: #4b5563; padding-left: 15px; margin-top: 2px; }
        .footer-table { margin-top: 30px; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 11px; color: #64748b; }
        
        /* Pre-printed Pad Letterhead Mode */
        body.letterhead-mode .header-table { display: none; }
        body.letterhead-mode .letterhead-spacer { display: block !important; }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background:#0891b2; padding:12px 20px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:10px; margin-bottom:20px; color:#fff;">
        <div style="font-weight:bold; font-size:13px;">
            Prescription #{{ $prescription->prescription_no }} &nbsp;|&nbsp; Patient: {{ $prescription->patient->name }}
        </div>
        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
            <!-- Letterhead Pad Mode Toggle -->
            <label style="font-size:11px; background:#0e7490; padding:5px 10px; border-radius:4px; cursor:pointer; display:flex; align-items:center; gap:5px;">
                <input type="checkbox" id="togglePad" onchange="document.body.classList.toggle('letterhead-mode', this.checked)">
                Pre-printed Pad Mode
            </label>

            <!-- Letterhead Margin Adjuster -->
            <select id="padMargin" onchange="document.getElementById('spacer').style.height = this.value" style="font-size:11px; padding:4px 6px; border-radius:4px; background:#fff; color:#1f2937; border:none;">
                <option value="40mm">Pad Gap: 40mm</option>
                <option value="50mm" selected>Pad Gap: 50mm</option>
                <option value="60mm">Pad Gap: 60mm</option>
                <option value="70mm">Pad Gap: 70mm</option>
            </select>

            <!-- WhatsApp Direct Share -->
            @php
                $patientPhone = preg_replace('/[^0-9]/', '', (string)$prescription->patient->phone);
                if (str_starts_with($patientPhone, '01')) {
                    $patientPhone = '88' . $patientPhone;
                }
                $waText = urlencode("Hello " . $prescription->patient->name . ",\nYour digital prescription Rx #" . $prescription->prescription_no . " from " . $prescription->doctor_name . " at " . $prescription->chamber_name . " is ready.\n\nView or download: " . route('prescription.print', $prescription->id));
            @endphp
            @if($patientPhone)
                <a href="https://wa.me/{{ $patientPhone }}?text={{ $waText }}" target="_blank" style="padding:6px 12px; background:#22c55e; color:#fff; font-weight:bold; font-size:11px; text-decoration:none; border-radius:4px; display:inline-flex; align-items:center; gap:4px;">
                    💬 Send WhatsApp
                </a>
            @endif

            @if($prescription->investigations)
                <a href="{{ route('prescription.investigations', $prescription->id) }}" target="_blank" style="padding:6px 14px; background:#6366f1; color:#fff; font-weight:bold; font-size:11px; text-decoration:none; border-radius:4px;">🧪 Lab Slip</a>
            @endif
            <button onclick="window.print()" style="padding:6px 14px; background:#fff; color:#0891b2; font-weight:bold; font-size:11px; border:none; border-radius:4px; cursor:pointer;">Print Now</button>
            <a href="{{ route('prescription.pdf', $prescription->id) }}" style="padding:6px 14px; background:#0e7490; color:#fff; font-weight:bold; font-size:11px; text-decoration:none; border-radius:4px;">Download PDF</a>
            <a href="{{ route('prescription.builder') }}" style="padding:6px 14px; background:#164e63; color:#fff; font-weight:bold; font-size:11px; text-decoration:none; border-radius:4px;">Back to Studio</a>
        </div>
    </div>

    <!-- Letterhead Spacer for physical pre-printed hospital pads -->
    <div id="spacer" class="letterhead-spacer" style="display:none; height:50mm;"></div>

    <!-- Chamber & Doctor Header Table -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="doc-name">{{ $prescription->doctor_name }}</div>
                <div class="doc-qual">{{ $prescription->doctor_qualification }}</div>
                @if($prescription->doctor_designation)
                    <div class="doc-desig">{{ $prescription->doctor_designation }}</div>
                @endif
                @if($prescription->chamber && $prescription->chamber->specialty)
                    <div style="font-size: 11px; color: #0891b2; font-weight: bold; margin-top: 2px;">Specialty: {{ $prescription->chamber->specialty }}</div>
                @endif
                <div style="font-size: 11px; color: #6b7280; margin-top: 3px;">Bikrify Verified Medical Practitioner</div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="chamber-title">{{ $prescription->chamber_name }}</div>
                @if($prescription->chamber_address)
                    <div class="chamber-info">{{ $prescription->chamber_address }}</div>
                @endif
                @if($prescription->chamber && $prescription->chamber->room_number)
                    <div class="chamber-info">Room/Chamber: {{ $prescription->chamber->room_number }}</div>
                @endif
                @if($prescription->chamber_phone)
                    <div class="chamber-info">Serial Hotline: {{ $prescription->chamber_phone }}</div>
                @endif
                @if($prescription->chamber && $prescription->chamber->whatsapp_number)
                    <div class="chamber-info">WhatsApp Serial: {{ $prescription->chamber->whatsapp_number }}</div>
                @endif
                @if($prescription->chamber_visiting_hours)
                    <div class="chamber-info">Hours: {{ $prescription->chamber_visiting_hours }}</div>
                @endif
                <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                    <strong>Rx No:</strong> {{ $prescription->prescription_no }} |
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($prescription->prescription_date)->format('d M, Y') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Patient Info Bar Table -->
    <div class="patient-strip">
        <table>
            <tr>
                <td class="patient-cell" style="width: 32%;"><strong>Patient:</strong> {{ $prescription->patient->name }}</td>
                <td class="patient-cell" style="width: 18%;"><strong>Age:</strong> {{ $prescription->patient->age }} Y</td>
                <td class="patient-cell" style="width: 20%;"><strong>Gender:</strong> {{ $prescription->patient->gender }}</td>
                <td class="patient-cell" style="width: 30%; text-align: right;"><strong>Patient ID:</strong> {{ $prescription->patient->patient_id }}</td>
            </tr>
        </table>
    </div>

    <!-- Main Body Table -->
    <table class="body-table">
        <tr>
            <!-- Left Clinical Details -->
            <td class="left-col">
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
            </td>

            <!-- Right Rx Section -->
            <td class="right-col">
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
                    <div class="section-title" style="margin-top: 25px;">Doctor's Advice</div>
                    <div style="white-space: pre-line;">{{ $prescription->advice }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Sign-off Footer Table -->
    <table class="footer-table">
        <tr>
            <td style="vertical-align: bottom;">
                Powered by <strong>Bikrify Digital Health Platform</strong>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                Doctor's Signature: _________________________
            </td>
        </tr>
    </table>
</body>
</html>
