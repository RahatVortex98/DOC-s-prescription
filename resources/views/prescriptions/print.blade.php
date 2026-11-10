@php
    $tenant = $prescription->tenant;
    $patient = $prescription->patient;
    $brandColor = $tenant?->brand_color ?: '#1e3fae';
    $bmdcNo = $prescription->doctor_bmdc_no ?: $tenant?->bmdc_no;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - {{ $prescription->prescription_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1f2937; margin: 0; padding: 0; font-size: 13px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        .header-table { border-bottom: 2px solid {{ $brandColor }}; padding-bottom: 10px; margin-bottom: 10px; }
        .doc-name { font-size: 18px; font-weight: bold; color: {{ $brandColor }}; }
        .doc-qual { font-size: 12px; color: #4b5563; margin-top: 2px; }
        .doc-desig { font-size: 11px; color: {{ $brandColor }}; font-weight: 600; margin-top: 2px; }
        .chamber-title { font-weight: bold; font-size: 15px; color: {{ $brandColor }}; }
        .chamber-info { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .patient-strip { background: #f0f5ff; border: 1px solid #dbeafe; padding: 8px 12px; margin: 10px 0 15px 0; border-radius: 6px; }
        .patient-cell { font-size: 12px; color: #1e293b; }
        .body-table { min-height: 650px; }
        .left-col { width: 35%; border-right: 1.5px solid #e2e8f0; padding-right: 15px; vertical-align: top; }
        .right-col { width: 65%; padding-left: 20px; vertical-align: top; }
        .section-title { font-weight: bold; text-transform: uppercase; font-size: 11px; color: {{ $brandColor }}; margin-top: 15px; margin-bottom: 5px; }
        .rx-symbol { font-size: 32px; font-weight: bold; font-family: serif; color: {{ $brandColor }}; line-height: 1; margin-bottom: 15px; }
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
    <div class="no-print" style="background:{{ $brandColor }}; padding:12px 20px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:10px; margin-bottom:20px; color:#fff;">
        <div style="font-weight:bold; font-size:13px;">
            Prescription #{{ $prescription->prescription_no }} &nbsp;|&nbsp; Patient: {{ $patient?->name ?? 'Patient' }}
        </div>
        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
            <!-- Letterhead Pad Mode Toggle -->
            <label style="font-size:11px; background:rgba(0,0,0,0.2); padding:5px 10px; border-radius:4px; cursor:pointer; display:flex; align-items:center; gap:5px;">
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
                $patientPhone = preg_replace('/[^0-9]/', '', (string)($patient?->phone ?? ''));
                if (str_starts_with($patientPhone, '01')) {
                    $patientPhone = '88' . $patientPhone;
                }
                $shareUrl = $prescription->public_id 
                    ? \Illuminate\Support\Facades\URL::temporarySignedRoute('prescription.public_share', now()->addDays(7), ['public_id' => $prescription->public_id])
                    : route('prescription.print', $prescription->id);
                $waText = urlencode("Hello " . ($patient?->name ?? 'Patient') . ",\nYour digital prescription Rx #" . $prescription->prescription_no . " from " . $prescription->doctor_name . " at " . $prescription->chamber_name . " is ready.\n\nView or download: " . $shareUrl);
            @endphp
            @if($patientPhone)
                <a href="https://wa.me/{{ $patientPhone }}?text={{ $waText }}" target="_blank" style="padding:6px 12px; background:#22c55e; color:#fff; font-weight:bold; font-size:11px; text-decoration:none; border-radius:4px; display:inline-flex; align-items:center; gap:4px;">
                    💬 Send WhatsApp
                </a>
            @endif

            @if($prescription->investigations)
                <a href="{{ route('prescription.investigations', $prescription->id) }}" target="_blank" style="padding:6px 14px; background:#6366f1; color:#fff; font-weight:bold; font-size:11px; text-decoration:none; border-radius:4px;">🧪 Lab Slip</a>
            @endif
            <button onclick="window.print()" style="padding:6px 14px; background:#fff; color:{{ $brandColor }}; font-weight:bold; font-size:11px; border:none; border-radius:4px; cursor:pointer;">Print Now</button>
            <a href="{{ route('prescription.pdf', $prescription->id) }}" style="padding:6px 14px; background:rgba(0,0,0,0.25); color:#fff; font-weight:bold; font-size:11px; text-decoration:none; border-radius:4px;">Download PDF</a>
            <a href="{{ route('prescription.builder') }}" style="padding:6px 14px; background:rgba(0,0,0,0.4); color:#fff; font-weight:bold; font-size:11px; text-decoration:none; border-radius:4px;">Back to Studio</a>
        </div>
    </div>

    @if($prescription->isVoided())
        <div style="position: fixed; top: 35%; left: 8%; width: 84%; text-align: center; font-size: 110px; font-weight: 900; color: rgba(220, 38, 38, 0.20); transform: rotate(-30deg); text-transform: uppercase; letter-spacing: 16px; border: 8px solid rgba(220, 38, 38, 0.20); padding: 20px; border-radius: 12px; pointer-events: none; z-index: 9999;">
            VOID
            <div style="font-size: 14px; letter-spacing: 2px; margin-top: 10px; color: rgba(220, 38, 38, 0.45);">
                VOIDED ON {{ $prescription->voided_at?->format('d M, Y h:i A') }} @if($prescription->void_reason) - REASON: {{ $prescription->void_reason }} @endif
            </div>
        </div>
    @endif

    <!-- Letterhead Spacer for physical pre-printed hospital pads -->
    <div id="spacer" class="letterhead-spacer" style="display:none; height:50mm;"></div>

    <!-- Chamber & Doctor Header Table -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                @if($tenant?->logo_path)
                    <div style="margin-bottom: 8px;">
                        <img src="{{ asset('storage/' . $tenant->logo_path) }}" alt="Logo" style="max-height: 48px; max-width: 180px; object-fit: contain;">
                    </div>
                @else
                    <div style="margin-bottom: 8px; display: inline-flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/bikrify-logo.png') }}" alt="Bikrify" style="height: 32px; width: 32px; border-radius: 6px; vertical-align: middle;">
                        <span style="font-weight: 900; font-size: 15px; color: #1e3fae; vertical-align: middle;">Bikrify<span style="color: #ff6200;">Care</span></span>
                    </div>
                @endif
                <div class="doc-name">{{ $prescription->doctor_name }}</div>
                <div class="doc-qual">{{ $prescription->doctor_qualification }}</div>
                @if($prescription->doctor_designation)
                    <div class="doc-desig">{{ $prescription->doctor_designation }}</div>
                @endif
                @if($bmdcNo)
                    <div style="font-size: 11px; color: #4b5563; font-weight: 600; margin-top: 2px;">BMDC Reg. No: {{ $bmdcNo }}</div>
                @endif
                @if($prescription->chamber && $prescription->chamber->specialty)
                    <div style="font-size: 11px; color: {{ $brandColor }}; font-weight: bold; margin-top: 2px;">Specialty: {{ $prescription->chamber->specialty }}</div>
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
                <td class="patient-cell" style="width: 32%;"><strong>Patient:</strong> {{ $patient?->name ?? 'Patient' }}</td>
                <td class="patient-cell" style="width: 18%;"><strong>Age:</strong> {{ $patient?->age ?? '-' }} Y</td>
                <td class="patient-cell" style="width: 20%;"><strong>Gender:</strong> {{ $patient?->gender ?? '-' }}</td>
                <td class="patient-cell" style="width: 30%; text-align: right;"><strong>Patient ID:</strong> {{ $patient?->patient_id ?? '-' }}</td>
            </tr>
            <tr>
                <td colspan="4" style="padding-top: 5px; font-size: 11px; border-top: 1px dashed #cbd5e1; margin-top: 4px;">
                    <strong style="color: #dc2626;">Drug Allergies:</strong>
                    @if($patient?->allergies)
                        <span style="color: #dc2626; font-weight: bold; background: #fee2e2; padding: 1px 6px; border-radius: 3px;">{{ $patient->allergies }}</span>
                    @else
                        <span style="color: #059669; font-weight: 500;">NKDA (No Known Drug Allergies)</span>
                    @endif
                </td>
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
                Powered by <strong>BikrifyCare Clinical Platform</strong>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                @if($tenant?->signature_path)
                    <div style="margin-bottom: 4px;">
                        <img src="{{ asset('storage/' . $tenant->signature_path) }}" alt="Doctor Signature" style="max-height: 45px; display: inline-block;">
                    </div>
                    <div style="font-size: 11px; color: #4b5563;">Authorized Doctor's Signature</div>
                @else
                    Doctor's Signature: _________________________
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
