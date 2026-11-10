@php
    $tenant = $prescription->tenant;
    $brandColor = $tenant?->brand_color ?: '#1e3fae';
    $bmdcNo = $prescription->doctor_bmdc_no ?: $tenant?->bmdc_no;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Investigation Requisition - {{ $prescription->prescription_no }}</title>
    <style>
        @page { size: A5 portrait; margin: 10mm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 15px;
            font-size: 13px;
            line-height: 1.4;
            background: #f8fafc;
        }
        .slip-card {
            background: #ffffff;
            border: 2px solid {{ $brandColor }};
            border-radius: 12px;
            padding: 20px;
            max-width: 550px;
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        table { width: 100%; border-collapse: collapse; }
        .header { border-bottom: 2px solid {{ $brandColor }}; padding-bottom: 10px; margin-bottom: 12px; }
        .slip-title {
            background: {{ $brandColor }};
            color: #fff;
            display: inline-block;
            padding: 3px 12px;
            font-size: 11px;
            font-weight: 800;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .doc-name { font-size: 16px; font-weight: bold; color: {{ $brandColor }}; }
        .doc-qual { font-size: 11px; color: #4b5563; }
        .doc-desig { font-size: 11px; color: {{ $brandColor }}; font-weight: 600; }
        .patient-strip {
            background: #f0fdfa;
            border: 1px solid #ccfbf1;
            padding: 8px 10px;
            margin: 10px 0 15px 0;
            border-radius: 6px;
            font-size: 11px;
        }
        .test-list {
            margin: 15px 0;
            padding: 0;
            list-style: none;
        }
        .test-item {
            padding: 8px 10px;
            border-bottom: 1px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            font-size: 13px;
            color: #0f172a;
        }
        .test-item:last-child { border-bottom: none; }
        .checkbox {
            width: 16px;
            height: 16px;
            border: 1.5px solid {{ $brandColor }};
            border-radius: 3px;
            display: inline-block;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 10px;
            font-size: 11px;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .no-print {
            max-width: 550px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            background: {{ $brandColor }};
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 12px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
            .slip-card { border: none; box-shadow: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('prescription.print', $prescription->id) }}" class="btn" style="background:#475569;">⬅ Back to Prescription</a>
        <button onclick="window.print()" class="btn">🖨️ Print Lab Slip</button>
    </div>

    <div class="slip-card">
        <!-- Title & Doctor Header -->
        <div class="header">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div class="slip-title">Diagnostic Test Requisition Slip</div>
                    <div class="doc-name">{{ $prescription->doctor_name }}</div>
                    <div class="doc-qual">{{ $prescription->doctor_qualification }}</div>
                    @if($prescription->doctor_designation)
                        <div class="doc-desig">{{ $prescription->doctor_designation }}</div>
                    @endif
                    @if($bmdcNo)
                        <div style="font-size: 10px; color: #4b5563; font-weight: 600; margin-top: 2px;">BMDC Reg. No: {{ $bmdcNo }}</div>
                    @endif
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:bold; font-size:13px; color:{{ $brandColor }};">{{ $prescription->chamber_name }}</div>
                    <div style="font-size:10px; color:#6b7280;">Rx: {{ $prescription->prescription_no }}</div>
                    <div style="font-size:10px; color:#6b7280;">Date: {{ \Carbon\Carbon::parse($prescription->prescription_date)->format('d M, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Patient Info Strip -->
        <div class="patient-strip">
            <table>
                <tr>
                    <td style="width:35%;"><strong>Patient:</strong> {{ $prescription->patient->name }}</td>
                    <td style="width:20%;"><strong>Age:</strong> {{ $prescription->patient->age }} Y</td>
                    <td style="width:20%;"><strong>Gender:</strong> {{ $prescription->patient->gender }}</td>
                    <td style="width:25%; text-align:right;"><strong>ID:</strong> {{ $prescription->patient->patient_id }}</td>
                </tr>
            </table>
        </div>

        @if($prescription->diagnosis)
            <div style="font-size:12px; margin-bottom:10px; color:#334155;">
                <strong>Provisional Diagnosis:</strong> <span style="color:#0891b2; font-weight:bold;">{{ $prescription->diagnosis }}</span>
            </div>
        @endif

        <!-- Tests List -->
        <div style="font-weight:bold; font-size:12px; text-transform:uppercase; color:#0891b2; margin-top:15px; border-bottom:1px solid #0891b2; padding-bottom:4px;">
            Requested Pathological & Radiological Investigations:
        </div>

        @php
            $rawTests = $prescription->investigations;
            $testItems = preg_split('/[,\n]+/', $rawTests);
        @endphp

        <ul class="test-list">
            @foreach($testItems as $idx => $test)
                @php $test = trim(ltrim($test, '•- ')); @endphp
                @if($test !== '')
                    <li class="test-item">
                        <span>{{ $idx + 1 }}. {{ $test }}</span>
                        <span class="checkbox"></span>
                    </li>
                @endif
            @endforeach
        </ul>

        <div style="font-size:11px; color:#64748b; font-style:italic; margin-top:15px; background:#f8fafc; padding:6px 10px; border-radius:4px;">
            * Note to Pathology / Diagnostic Center: Please perform the requested tests with standard reference ranges and provide reports for clinical correlation.
        </div>

        <!-- Doctor Signature Footer -->
        <div class="footer">
            <div>Powered by <strong>BikrifyCare Clinical Platform</strong></div>
            <div style="text-align:right;">
                Doctor's Signature: _________________________
            </div>
        </div>
    </div>
</body>
</html>
