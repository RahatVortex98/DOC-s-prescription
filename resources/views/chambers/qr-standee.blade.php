<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chamber QR Standee - {{ $chamber->doctor_name }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .poster-card {
            width: 100%;
            max-width: 650px;
            background: #ffffff;
            border: 3px solid #0891b2;
            border-radius: 20px;
            padding: 35px 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            text-align: center;
            box-sizing: border-box;
        }
        .top-badge {
            background: #0891b2;
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 6px 18px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 15px;
        }
        .doc-name {
            font-size: 26px;
            font-weight: 900;
            color: #0891b2;
            margin: 0 0 5px 0;
        }
        .doc-qual {
            font-size: 14px;
            color: #475569;
            margin: 0 0 4px 0;
            font-weight: 600;
        }
        .doc-desig {
            font-size: 13px;
            color: #0284c7;
            font-weight: 700;
            margin: 0 0 15px 0;
        }
        .hospital-box {
            background: #f0fdfa;
            border: 1.5px dashed #0891b2;
            border-radius: 12px;
            padding: 12px 18px;
            margin: 15px 0 25px 0;
            font-size: 13px;
        }
        .hospital-name {
            font-size: 16px;
            font-weight: 800;
            color: #0e7490;
        }
        .qr-wrapper {
            background: #ffffff;
            border: 3px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px;
            display: inline-block;
            margin: 10px 0 20px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .qr-wrapper img {
            width: 230px;
            height: 230px;
            display: block;
        }
        .scan-instruction-en {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .scan-instruction-bn {
            font-size: 15px;
            font-weight: 700;
            color: #0891b2;
            margin: 0 0 15px 0;
        }
        .direct-link {
            font-size: 12px;
            color: #64748b;
            font-family: monospace;
            background: #f1f5f9;
            padding: 6px 14px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .contact-strip {
            border-top: 1.5px solid #e2e8f0;
            padding-top: 15px;
            display: flex;
            justify-content: space-around;
            font-size: 13px;
            color: #334155;
            font-weight: 600;
        }
        .brand-footer {
            margin-top: 25px;
            font-size: 11px;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            background: #0891b2;
            color: white;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-outline {
            background: #ffffff;
            color: #0891b2;
            border: 2px solid #0891b2;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
            .poster-card { box-shadow: none; border: 2px solid #0891b2; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨️ Print Chamber Door Poster (A4)</button>
        <a href="{{ $bookingUrl }}" target="_blank" class="btn btn-outline">🔗 Open Patient Booking Link</a>
        <a href="{{ route('appointment.manager') }}" class="btn btn-outline">⬅ Back to Queue Desk</a>
    </div>

    <div class="poster-card">
        <!-- Specialty Header -->
        <div class="top-badge">{{ $chamber->specialty ?: 'Medical Specialist' }}</div>

        <!-- Doctor Details -->
        <h1 class="doc-name">{{ $chamber->doctor_name }}</h1>
        <div class="doc-qual">{{ $chamber->doctor_qualification }}</div>
        <div class="doc-desig">{{ $chamber->doctor_designation }}</div>

        <!-- Hospital Location & Visiting Hours -->
        <div class="hospital-box">
            <div class="hospital-name">{{ $chamber->name }}</div>
            @if($chamber->room_number)
                <div style="font-weight: 700; color: #0284c7; margin-top: 2px;">{{ $chamber->room_number }}</div>
            @endif
            <div style="color: #64748b; margin-top: 3px;">{{ $chamber->address }}</div>
            <div style="font-weight: 700; color: #0f172a; margin-top: 5px;">Visiting Hours: {{ $chamber->visiting_hours }}</div>
        </div>

        <!-- Scannable QR Code -->
        <div class="qr-wrapper">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data={{ urlencode($bookingUrl) }}" alt="Chamber QR Code for Patient Self-Booking">
        </div>

        <!-- Bilingual Instructions -->
        <div class="scan-instruction-en">📲 Scan with Phone Camera to Get Serial Token</div>
        <div class="scan-instruction-bn">আপনার মোবাইল ক্যামেরা দিয়ে স্ক্যান করে সিরিয়াল টোকেন নিন</div>

        <div class="direct-link">
            Web: {{ $bookingUrl }}
        </div>

        <!-- Serial Helpline & WhatsApp -->
        <div class="contact-strip">
            <div>📞 Serial: <strong>{{ $chamber->phone }}</strong></div>
            @if($chamber->whatsapp_number)
                <div>💬 WhatsApp: <strong>{{ $chamber->whatsapp_number }}</strong></div>
            @endif
        </div>

        <!-- Branding -->
        <div class="brand-footer">
            Powered by BikrifyCare Clinical Platform • Fast Offline-Ready Medical Prescriptions
        </div>
    </div>
</body>
</html>
