# BikrifyCare - Prescription Studio (DOC-s-prescription)

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Tests](https://img.shields.io/badge/PHPUnit-79%20Passing-brightgreen?style=for-the-badge&logo=github-actions&logoColor=white)](#automated-testing)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

**BikrifyCare Prescription Studio** is a commercial-grade, multi-tenant clinical Prescribing Platform and Patient Queue Management SaaS built specifically for doctors, hospital consultants, polyclinics, and receptionists in Bangladesh and global private practice.

---

## 🌟 Executive Feature Highlights

### 1. 🏢 Multi-Tenant SaaS Architecture & Isolation
One application, one shared database, with strict cryptographic and query-level isolation across independent doctor practices:
- **`tenant_id` Scoping**: All chambers, patients, prescriptions, appointments, templates, and drug kits are strictly scoped by `tenant_id`.
- **Global Tenant Scope (`BelongsToTenant`)**: Automatically scopes every Eloquent query to the authenticated doctor's clinic. Unauthorized cross-tenant queries resolve to `1 = 0` (zero rows) and trigger `404 Not Found`.
- **Three-Tier Role Hierarchy**:
  - `super_admin`: Provisions new doctor tenants, customizes doctor verified identity, toggles trial/active/suspended states, zero access to private patient medical records.
  - `doctor`: Complete clinical prescribing workspace, past patient history, diagnostics, voiding, and print/PDF streaming.
  - `assistant`: Front-desk patient intake and queue token management. Clinical findings and diagnoses are automatically masked/hidden from assistant view.
- **Tenant Status Enforcement**: Instantly blocks suspended clinic accounts at the middleware level, invalidating active sessions.

---

### 2. 🏛️ Super-Admin Doctor Credential Governance & Zero-Setup Friction
Doctors should never be burdened with administrative setup or database configuration:
- **Permanent Super-Admin Governance (`/admin/tenants`)**:
  - Doctor Verified Name, Medical Qualifications (`MBBS, FCPS, MD, BCS (Health)`), and BMDC Reg. No.
  - Institutional Designation & Department (e.g. *Associate Professor & Consultant, Nephrology*)
  - Hospital Affiliation, Chamber Room Number, Floor, Address, Visiting Hours, Serial Hotline, and WhatsApp Number.
  - Only Super Admin can modify verified credentials, preventing credential tampering.
- **Zero-Setup Doctor Experience**:
  - Unnecessary setup modals, "+ Hospital", and configuration hurdles have been removed from the doctor interface.
  - When the doctor logs in, their profile, hospital chambers, and default templates are already pre-loaded and ready for instant prescribing.

---

### 3. 🎯 Specialty-Driven Dynamic Clinical Workspaces
When a doctor works in their practice, the clinical workspace tailors Chief Complaints, Examination (O/E), Diagnoses, Laboratory Investigations, and Advice chips to their domain:
- **Nephrology / CKD Specialist**: Chronic Kidney Disease (Stage 1–5, ESRD), Hypertensive Nephrosclerosis, Diabetic Nephropathy, Glomerulonephritis, S. Creatinine, eGFR, Spot ACR, KUB USG, low-protein diets, and fluid limits.
- **Cardiology**: IHD, Angina, CCF, NSTEMI, Atrial Fibrillation, ECG, Echocardiography, Troponin-I, Lipid profiles, and cardiac advice.
- **General Medicine / Internal Medicine**: Acute viral syndromes, Typhoid/Enteric fever, UTI, Dengue NS1 workups, pneumonia, and fever management.
- **Gastroenterology**: GERD, PUD, IBS, Endoscopy, H. Pylori, USG of whole abdomen, and dietary acid reduction protocols.
- **Diabetology / Endocrinology**: Type 2 Diabetes Mellitus, Diabetic Neuropathy, HbA1c, microalbuminuria, lipid profiles, diabetic foot care, and blood sugar tracking.

---

### 4. ⚡ Less-Input Automation (Near-Zero Doctor Typing)
- **1-Click Full Clinical Bundles**: Clicking e.g. **"⚡ 1-Click CKD Stage 4 Full Profile"** instantly auto-fills Complaints, Examination, Diagnosis, Investigations, and Special Advice in **literally 1 click**.
- **1-Click Specialty Medication Kits**:
  - **💊 CKD Core Drug Kit (4 Meds)**: Adds `Ketosteril` (1+1+1 with meal), `Sodibicarb 500mg` (1+0+1 after meal), `Febux 40mg` (0+0+1 after meal), and `Calcichew D` (0+0+1 after meal).
  - **💊 Heart & BP Kit**: `Amlopin 5mg` + `Bizoran 5/20mg` + `Concor 2.5mg`.
  - **💊 Fever & Bodyache Kit**: `Napa Extra` + `Protonix 20mg` + `Alarid 10mg`.
  - **💊 Gastritis Relief Kit**: `Protonix 40mg` + `Motigut 10mg` + `Antacid Plus`.
  - **💊 Diabetes Starter Kit**: `Comet 500mg` + `Linaglip 5mg` + `Neobion`.
- **🩺 Fast Vitals Calculator**:
  - Enter BP Systolic/Diastolic (e.g. `160 / 95`) -> Auto-detects **Stage 2 HTN**.
  - Enter Pulse (`80 bpm`), RBS (`7.8 mmol/L`), Weight (`70 kg`), Height (`165 cm`) -> Auto-calculates **BMI (25.7 Overweight)**.
  - **"+ Insert into O/E"** inserts clean formatted vitals in 1 click.
- **📅 1-Click Follow-Up Date Generator**:
  - Click `[ + 7 Days ]`, `[ + 15 Days ]`, `[ + 1 Month ]`, or `[ + 3 Months ]` to compute the exact calendar return date (e.g. *Follow-up after 15 days on 25 Nov, 2026 with S. Creatinine report*) and append it to Doctor's Advice.

---

### 5. 🎙️ Continuous Voice Activate Studio & Bilingual Command AI
- **Continuous Voice Activation**: Doctors can toggle the **`🎙️ Voice Activate Studio`** banner on top of the prescription studio. Features live audio waveform visualization and instant speech parsing.
- **Bilingual Speech Engine**: Supports **English (Medical Terminology - `en-US`)** and **বাংলা (Bengali - `bn-BD`)** without paid third-party API costs.
- **Smart Natural Voice Commands**:
  - *"Complaint fever and cough"* / *"সমস্যা ৩ দিন ধরে জ্বর"* -> Auto-populates Chief Complaints.
  - *"Findings BP 140/90, Pulse 76"* / *"BP ১৪০ বাই ৯০"* -> Auto-populates Examination (O/E).
  - *"Diagnosis Acute Bronchitis"* / *"রোগ টাইফয়েড"* -> Auto-populates Diagnosis.
  - *"Investigation CBC, Chest X-ray, S. Creatinine"* / *"পরীক্ষা সিবিসি"* -> Auto-populates Investigations.
  - *"Medicine Napa Extra 1+0+1 5 days After meal"* -> Parses brand, form, dosage, duration, and instructions into a new medicine row.
  - *"Advice Drink plenty of water and avoid cold food"* / *"পরামর্শ বিশ্রাম নিন"* -> Auto-populates Doctor's Advice.
  - *"Save"* / *"Print"* / *"প্রিন্ট করো"* -> Automatically validates, saves, and opens the A4 print dialog.
- **Interactive Voice Cheat Sheet**: Modal guide (`? Voice Help`) displaying command syntax in English and Bengali.

---

### 6. 💊 Multi-Select Dropdowns & Fast Prescribing Controls
All medication inputs use multiple pre-configured dropdowns for rapid input:
- **Duration Dropdown (12 Options)**: `3 days`, `5 days`, `7 days`, `10 days`, `14 days`, `21 days`, `1 month`, `1.5 months`, `2 months`, `3 months`, `6 months`, `Continue (চলবে)`.
- **When to Take / Instructions Dropdown (12 Options)**: `After meal (খাবার পরে)`, `Before meal (খাবার আগে)`, `With meal (খাবারের সাথে)`, `Empty stomach (খালি পেটে)`, `At bedtime (ঘুমানোর আগে)`, `Before breakfast`, `After breakfast`, `30 min before meal`, `In the morning`, `At night`, `As needed (PRN)`, `As directed`.
- **Dosage Dropdown**: `1+0+1`, `1+1+1`, `1+0+0`, `0+1+0`, `0+0+1`, `1+1+1+1`, `1/2+0+1/2`, `1+0+0+1`, `2 Puffs`, `1 spoon`, `As needed`.
- **Form / Type Dropdown**: `Tab.`, `Cap.`, `Inj.`, `Syr.`, `Drop`, `Oint.`, `Inhaler`, `Supp.`, `Sachet`.

---

### 7. 📱 Main Feature A: QR-Based Patient Self-Booking Portal & Printable Standee
Solo practitioners, clinic chains, and private hospital consultants can completely automate patient intake without hiring front-desk staff:
- **Unique Doctor Booking URLs (`/{tenant:slug}`)**:
  - Example: `https://prescriptions.bikrify.com/dr-mahbubur`
  - Mobile-optimized, zero-app-install patient portal displaying doctor qualifications, visiting hours, and chamber locations.
  - Required medical data privacy consent checkbox before submission.
  - Date & schedule validation rejecting past dates (`after_or_equal:today`) and closed chamber days.
  - Strict IP rate limiting: 30 requests/min and maximum 5 bookings per 60 seconds per IP.
  - Instantly issues an electronic **Serial Token Card** with live waiting counter and unique booking reference (e.g. `BK-8F3A19`).
- **Printable Chamber Door QR Standee Poster (`/app/chambers/{chamber}/qr-standee`)**:
  - Professional A4 standee poster featuring verified Doctor credentials, chamber address, visiting schedule, hotline, and an **ultra-sharp QR Code** linking directly to the doctor's public portal.
  - High-visibility bilingual patient instructions: *"Scan with Phone Camera to Get Serial Token"* / *"আপনার মোবাইল ক্যামেরা দিয়ে স্ক্যান করে সিরিয়াল টোকেন নিন"*.
  - Ready for 1-click printing, laminating, and acrylic mounting outside hospital chamber doors.

---

### 8. 💬 Main Feature B: Individual Meta WhatsApp Cloud API Integration
Each doctor or clinic connects **their own dedicated WhatsApp Business Phone Number** directly to the application. The system **never routes messages through a single shared number**:
- **Meta Embedded Signup & Manual Connect (`/app/settings/whatsapp`)**:
  - Direct integration with Meta Graph API (`v22.0`).
  - Supports Meta Embedded Signup popup flow (`FB.login` with `whatsapp_business_management`, `whatsapp_business_messaging`).
  - System User token exchange exchanging short-lived frontend tokens for long-lived credentials.
  - Direct Manual Token Entry option for advanced enterprise clinics and managed setups.
- **Enterprise Multi-Tenant Security & Credential Encryption**:
  - Meta System User Access Tokens are encrypted at rest using AES-256-CBC via Eloquent's `'encrypted'` model casting.
  - Access tokens are strictly isolated per `tenant_id` and **never exposed in frontend HTML, Livewire snapshots, or client-side JavaScript**.
  - WABA ID, Phone Number ID, Display Phone Number, and Verified Name are verified and tracked.
  - Automatic Webhook Subscription (`POST /{waba-id}/subscribed_apps`) during onboarding handshake.
  - 1-Click Disconnect & Revocation workflow with audit logging (`whatsapp.disconnected`).

---

### 9. ⚡ Centralized Multi-Tenant Webhook Architecture
A single, highly optimized webhook receiver (`POST /webhooks/whatsapp`) handles incoming traffic for all connected doctors with strict cryptographic verification and background asynchronous queue processing:
- **Meta Handshake Verification (`GET /webhooks/whatsapp`)**:
  - Validates `hub.mode === 'subscribe'` and matches `hub.verify_token` against `config('services.meta.webhook_verify_token')`. Returns `hub.challenge` synchronously.
- **HMAC-SHA256 Signature Verification (`POST /webhooks/whatsapp`)**:
  - Validates incoming `X-Hub-Signature-256` against `hash_hmac('sha256', $rawPayload, $appSecret)`. Spoofed payloads or incorrect secrets are rejected instantly with `401 Unauthorized`.
- **Idempotency Deduplication Ledger (`webhook_events`)**:
  - Hashes raw payloads and logs every event in `webhook_events`.
  - Duplicate payloads or webhook retries return `200 OK` immediately without re-dispatching jobs.
- **Multi-Tenant Routing by `phone_number_id`**:
  - Inbound messages extract Meta's recipient `phone_number_id` from the payload metadata.
  - Resolves the exact `WhatsAppIntegration` and `Tenant` without needing URL slugs or domain spoofing.
- **Asynchronous Queue Pipeline**:
  - Returns `200 OK` to Meta servers within milliseconds to prevent webhook timeouts.
  - Dispatches `ProcessWhatsAppWebhook` job onto the queue.
  - Dispatches `SendWhatsAppMessage` job for rate-limited, resilient outbound API delivery.

---

### 10. 🤖 Deterministic WhatsApp Appointment Booking State Machine
Patients messaging the doctor's WhatsApp number are guided through an intuitive, deterministic 5-step interactive appointment booking flow:
- **Conversation State Flow**:
  1. `IDLE` / `AWAITING_NAME`: Welcomes patient with clinic branding and prompts for patient full name.
  2. `AWAITING_CHAMBER`: Displays doctor's practice locations and prompts patient to select chamber number (e.g. `1` or `2`). Auto-selected if doctor has a single chamber.
  3. `AWAITING_DATE`: Prompts for appointment date (accepts `TODAY`, `TOMORROW`, or `YYYY-MM-DD`). Automatically validates against doctor chamber closed days.
  4. `AWAITING_SLOT`: Calculates real-time remaining slot capacity and prompts for slot selection (e.g. `1. Morning`, `2. Evening`).
  5. `AWAITING_CONFIRMATION`: Summarizes Doctor Name, Chamber, Date, Time, and Patient details. Patient replies `YES` or `CONFIRM` to finalize.
  6. `CONFIRMED`: Calls `AppointmentService`, atomically issues a serial number and booking reference (e.g. `BK-WA7B4C`), and dispatches instant WhatsApp confirmation.
- **Self-Service Commands**:
  - `STATUS`: Instant lookup of today's live queue status, serial number, and doctor visiting hours.
  - `CANCEL`: Cancels active booking and frees the slot in the queue.
  - `RESCHEDULE` or `RESET`: Clears active booking draft and restarts booking flow.
  - `HELP` / `MENU`: Displays interactive command directory.
- **Clinical Guardrails & Medical Safety Disclaimer**:
  - If a patient sends symptom descriptions or requests prescriptions via WhatsApp, the bot replies with a prominent medical disclaimer:
    > *"⚠️ Disclaimer: BikrifyCare WhatsApp Bot is an automated appointment scheduling assistant and cannot give medical advice or diagnose illnesses. For medical emergencies, please visit the nearest hospital emergency room."*
- **Front-Desk Human Takeover**:
  - Patients can type `HUMAN`, `AGENT`, or `HELP` to pause automated bot replies.
  - Conversation status updates to `waiting_human`, alerting clinic staff in the WhatsApp Clinical Inbox.
  - When staff sends a manual reply from the dashboard, bot replies are automatically paused.

---

### 11. 🩺 Unified Appointment Engine (`AppointmentService`)
All appointment bookings—whether originating from **QR Standee scan**, **WhatsApp automated bot**, or **Receptionist walk-in desk**—are processed through a single, hardened service class:
- **Atomic Serial Generation**: Employs `DB::transaction()` with pessimistic `lockForUpdate()` querying `appointments->where('appointment_date', ...)->max('serial_no') + 1` to guarantee zero duplicate serials during simultaneous traffic bursts.
- **Collision-Proof Booking References**: Generates cryptographically secure alphanumeric references (e.g. `BK-WA89B1`, `BK-QR44D2`, `BK-RC10F5`) with automatic database collision checks.
- **Acquisition Channel Tracking**: Automatically stamps `booking_source` (`qr`, `whatsapp`, `receptionist`) and links `conversation_id`.
- **Patient Disambiguation**: Resolves patient by phone and normalized name, creating new patient records when family members share a mobile number.

---

### 12. 💬 Live WhatsApp Clinical Inbox & Practice Analytics
- **Live WhatsApp Clinical Inbox (`/app/whatsapp/inbox`)**:
  - Two-pane clinical chat interface:
    - **Left Pane**: Patient conversation threads, search by name/phone, filtering by `All`, `Staff Needed (Takeover)`, and `Bot Active`.
    - **Right Pane**: Message transcript with delivered/read receipts, patient clinical summary, active takeover toggle (`Take Over Chat` / `Return to Bot`), and manual reply input.
  - Sending a manual reply automatically activates human takeover mode and dispatches via Meta Cloud API.
- **Acquisition Channel Filter on Queue Desk (`/app/appointments`)**:
  - Channel filter pills on appointment manager: `All`, `WhatsApp`, `QR Standee`, and `Front-Desk`.
  - Shows real-time booking count per acquisition channel.
  - Displays color-coded badges for channel and booking reference on patient queue cards.
- **Practice Acquisition Channel Breakdown (`/app/dashboard`)**:
  - Practice Analytics dashboard widget showing percentage and volume breakdown:
    - 💬 WhatsApp Bot bookings (% and total count)
    - 📱 QR Standee bookings (% and total count)
    - 🏢 Receptionist / Front-Desk walk-ins (% and total count)

---

### 13. 🛡️ Clinical Safety, Drug Allergies & Prescription Voiding
- **Prominent Drug Allergies**:
  - `allergies` field on `patients` table.
  - Displayed prominently in **HIGH-VISIBILITY RED** inside the prescription studio next to patient demographics.
  - Printed on both physical prescriptions and PDFs right under the demographic strip (`Drug Allergies: Penicillin, Sulfa` or `NKDA - No Known Drug Allergies`).
- **Prescription Voiding Workflow**:
  - Doctors can mark erroneous or superseded prescriptions as **VOID** with an explicit cancellation reason.
  - Voided prescriptions are permanently stamped with an angled, prominent **"VOID"** watermark on screen and print.
  - Prevents accidental re-use, re-issuance, or cloning of canceled prescriptions.
- **Soft Deletes Protection**:
  - `Patient`, `Prescription`, and `Appointment` models utilize Eloquent Soft Deletes.
  - Accidental deletion retains all medical history and audit trails safely in the database.

---

### 9. 🔐 Expiring Signed Share Links & Lightweight Audit Trail
- **Expiring 7-Day Signed Patient Links (`/rx/{public_id}`)**:
  - Digital prescription links sent via WhatsApp use non-guessable ULID tokens (`public_id`) signed cryptographically with a 7-day expiration timestamp.
  - Internal database IDs and doctor backend URLs are never exposed.
  - Tampered, altered, or expired URLs automatically return `403 Forbidden`.
- **Lightweight Audit Trail (`/app/audit-logs`)**:
  - Tracks critical events: `prescription.created`, `prescription.printed`, `prescription.pdf_downloaded`, `prescription.voided`, `prescription.deleted`, and `appointment.deleted`.
  - Captures exact actor (`user_id`), clinic (`tenant_id`), timestamp, client IP, and event metadata.
  - Accessible to clinic doctors via the **Audit Logs** navigation tab.

---

### 10. 📦 Idempotent Medicine Database & Concurrency Protections
- **Collision-Safe RX Generation**:
  - Generates robust tokens (`RX-ymd-XXXXX`) wrapped in atomic DB transactions with up to 10 collision-retry attempts.
- **Family-Member Disambiguation**:
  - In Bangladesh, multiple family members frequently share a single mobile phone number.
  - Patient lookups strictly check `phone + trimmed case-insensitive name`. If only the phone matches, a new distinct patient record is automatically created rather than overwriting family members' medical history.
- **Idempotent Medicine Import**:
  - `php artisan app:import-medicines` / `php artisan medicines:import` uses database upserts on `[brand_name, strength, dosage_form]`. Re-running the command never creates duplicate medicines.

---

### 11. 🖨️ A4 Prescription Printing & PDF Streaming
- **Dual-Mode Prescription Output**:
  - **Full Print Mode**: Renders complete doctor letterhead, institutional credentials, hospital room number, patient details, clinical findings, medicines, and footer.
  - **Pre-printed Pad Mode (Letterhead Spacer)**: Toggle on if the doctor writes on a pre-printed hospital pad. Hides header and reserves an adjustable 40mm–70mm top margin.
- **A4 PDF Export**: Instant server-side PDF generation via DomPDF.
- **Diagnostic Lab Test Requisition Slip (`/app/prescriptions/{id}/investigations`)**: Separates lab tests onto a dedicated investigation requisition slip with clinical indications.

---

### 12. 📊 Practice Analytics Dashboard (`/app/dashboard`)
Doctor analytics workspace scoped strictly to the authenticated tenant:
- **KPI Metrics**: Total Prescriptions written, Prescriptions today, Prescriptions this month, Total Patients registered, Appointments queued today, Active chambers.
- **Top Diagnoses Breakdown**: Visual frequency ranking of the doctor's most diagnosed medical conditions.
- **Top Prescribed Medications**: Most frequently prescribed drugs and dosage forms.
- **Patient Gender Demographics**: Male vs. Female vs. Other ratio.
- **Chamber Volume Comparison**: Prescription volume distribution across multiple practice locations.

---

## 🛠️ Tech Stack & Architecture

| Layer | Technology | Version | Notes |
| :--- | :--- | :--- | :--- |
| **Backend Framework** | Laravel | `^13.17` (v13.40.0) | Multi-tenant Eloquent architecture |
| **Full-Stack UI** | Laravel Livewire | `^4.4` (v4.4.8) | Reactive, single-page experience |
| **Runtime** | PHP | `8.4.25` | Modern typed PHP |
| **Frontend Styling** | Tailwind CSS | `v4.0` | Clean medical-grade UI |
| **PDF Engine** | Barryvdh DomPDF | `^3.1` | Native A4 PDF generation |
| **Database** | SQLite / MySQL / PostgreSQL | Compatible | Single DB with `tenant_id` scoping |
| **Testing** | PHPUnit | `^12.5` | 79 comprehensive feature, safety, isolation & WhatsApp tests |
| **Code Formatter** | Laravel Pint | `^1.27` | Strict Laravel preset |

---

## 🚀 Quickstart & Installation

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/RahatVortex98/DOC-s-prescription.git
cd DOC-s-prescription

# Install PHP dependencies
composer install

# Install NPM dependencies & build frontend assets
npm install
npm run build

# Configure environment
cp .env.example .env
php artisan key:generate
```

### 2. Configure Meta WhatsApp Cloud API (Optional for Local/Dev)
In `.env`, configure your Meta Developer credentials:
```env
META_APP_ID=your_meta_app_id
META_APP_SECRET=your_meta_app_secret
META_WEBHOOK_VERIFY_TOKEN=your_custom_webhook_secret_verify_token
META_API_VERSION=v22.0
META_CONFIG_ID=
```

### 3. Run Database Migrations & Seeders
```bash
# Creates database and runs all migrations + default demo accounts
touch database/database.sqlite
php artisan migrate --seed
```

The database seeder automatically creates:
- 1 Doctor tenant clinic (`dr-mahbubur`).
- 3 Practice Hospital Chambers (*Bikrify Banani*, *Popular Dhanmondi*, *Square Hospital*).
- 90+ verified DGDA pharmaceutical brands and full variant suites.
- Today's sample patient queue appointments.
- 3 login accounts for immediate testing.

---

## 🔑 Doctor Demo Access & Seeded Accounts

The login portal at **`http://localhost:8000/login`** features an instant **⚡ 1-Click Doctor Demo** button tailored specifically for client demonstrations:

- **Doctor Interactive Demo (1-Click)**: Clicking **`[ 🩺 Launch Doctor Demo (1-Click) ]`** logs in immediately as `doctor@bikrify.com` (`password`) and opens the full Prescription Studio with active patients, vitals calculator, voice dictation, and drug kits.
- **Other Seeded Accounts** (Log in via standard credentials form):
  - **Super Admin**: `admin@bikrify.com` / `password` (Governance Portal at `/admin/tenants`)
  - **Assistant**: `assistant@bikrify.com` / `password` (Front-Desk Queue Desk at `/app/appointments`)

---

## 🔗 Key Application Routes

| Route | Name | Middleware | Description |
| :--- | :--- | :--- | :--- |
| `/login` | `login` | `guest` | Livewire authentication portal. |
| `/app` | `prescription.builder` | `auth, tenant.active` | Main Prescription Studio workspace. |
| `/app/dashboard` | `doctor.dashboard` | `auth, tenant.active` | Practice analytics dashboard & acquisition channel breakdown. |
| `/app/prescriptions` | `prescription.list` | `auth, tenant.active` | Prescription records, search, voiding, and reprint. |
| `/app/appointments` | `appointment.manager` | `auth, tenant.active` | Patient queue desk, booking references & channel filter pills. |
| `/app/whatsapp/inbox` | `whatsapp.inbox` | `auth, tenant.active` | Live two-way patient chat & front-desk human takeover desk. |
| `/app/settings/whatsapp`| `whatsapp.settings` | `auth, tenant.active`| Individual Meta WhatsApp Cloud API integration settings. |
| `/app/audit-logs` | `audit.logs` | `auth, tenant.active` | Security & clinical event audit log viewer. |
| `/app/chambers/{id}/qr-standee` | `chamber.qr_standee` | `auth, tenant.active` | Printable A4 chamber door QR standee poster. |
| `/app/prescriptions/{id}/print` | `prescription.print` | `auth, tenant.active` | A4 prescription print & letterhead view. |
| `/app/prescriptions/{id}/pdf` | `prescription.pdf` | `auth, tenant.active` | DomPDF download stream. |
| `/app/prescriptions/{id}/investigations`| `prescription.investigations` | `auth, tenant.active`| Lab test requisition slip. |
| `/rx/{public_id}` | `prescription.public_share` | `signed` | Secure 7-day expiring patient WhatsApp prescription link. |
| `/admin/tenants` | `admin.tenants` | `auth, role.super_admin` | Super-admin tenant provisioning & profile governance. |
| `/{tenant:slug}` | `appointment.public_book`| `throttle:30,1` | Public patient QR self-booking page (e.g. `/dr-mahbubur`). |
| `GET /webhooks/whatsapp`| — | `webhooks/*` | Meta webhook verification challenge handshake. |
| `POST /webhooks/whatsapp`| `whatsapp.webhook` | `webhooks/*` | Centralized Meta webhook receiver (HMAC-SHA256 verified). |

---

## 🧪 Automated Testing

This application includes a comprehensive test suite with **79 passing tests** and **332 assertions** covering all critical functional, architectural, and security domains:

### 🏢 Multi-Tenant Isolation & Security
- **Tenant Isolation**: Proves Tenant B cannot query, see, or search Tenant A's patients, prescriptions, appointments, or WhatsApp conversations.
- **Route Authorization**: Proves cross-tenant access to `/app/prescriptions/{id}/print`, `/pdf`, and `/investigations` returns `404 Not Found`.
- **Livewire ID Protection**: Proves passing another tenant's ID to Livewire actions returns `404 Not Found`.
- **Guest Protection**: Proves all unauthenticated requests to `/app/*` redirect to `/login`.
- **Suspension Enforcement**: Proves suspended tenants cannot access the studio or accept public bookings.
- **Assistant Role Security**: Proves front-desk assistants cannot see clinical diagnosis or examination findings.

### 📱 Unified Appointment Engine & QR Booking
- **Atomic Serial Numbers**: Proves `AppointmentService` generates concurrency-safe serial numbers without race condition duplicates.
- **Unique Booking References**: Proves collision-free `BK-XXXXXX` references across QR, WhatsApp, and Receptionist bookings.
- **Chamber Cross-Tenant Guard**: Proves booking fails if a chamber does not belong to the target tenant.
- **Public QR Booking**: Proves bookings at `/{tenant:slug}` write strictly into that tenant's records with IP rate limiting.
- **Acquisition Channel Filtering**: Proves `AppointmentManager` filters appointments by `qr`, `whatsapp`, and `receptionist` channels with live count badges.

### 💬 WhatsApp Cloud API & Integration Settings
- **Doctor Integration Settings**: Proves doctor can view setup, enter credentials, trigger OAuth exchange, and disconnect.
- **Encryption at Rest**: Proves Meta access tokens are encrypted in the database and never exposed to the frontend.
- **Audit Logging**: Proves connecting, updating, and disconnecting WhatsApp integrations logs audit events.

### ⚡ Centralized Webhooks & Queue Processing
- **Webhook Handshake**: Proves `GET /webhooks/whatsapp` validates `hub.verify_token` and echoes `hub.challenge`.
- **HMAC Signature Security**: Proves `POST /webhooks/whatsapp` validates `X-Hub-Signature-256` and rejects spoofed signatures with `401`.
- **Idempotency Deduplication**: Proves duplicate webhook payloads or message `wamid`s return `200 OK` without duplicate processing.
- **Queue Job Dispatching**: Proves webhooks extract `phone_number_id`, resolve tenant, and queue `ProcessWhatsAppWebhook`.

### 🤖 Automated WhatsApp Booking State Machine
- **5-Step Booking Flow**: Proves conversation advances deterministically: `AWAITING_NAME` → `AWAITING_CHAMBER` → `AWAITING_DATE` → `AWAITING_SLOT` → `AWAITING_CONFIRMATION` → `CONFIRMED`.
- **Schedule Validation**: Proves patient input is validated against chamber schedules and closed days.
- **Self-Service Commands**: Proves `STATUS`, `CANCEL`, `RESCHEDULE`, and `MENU` commands work seamlessly.
- **Medical Safety Disclaimer**: Proves symptom inquiries trigger the emergency disclaimer and advise visiting the ER.
- **Human Takeover Transition**: Proves typing `HUMAN` pauses the bot and flags conversation for staff in the inbox.

### 💬 WhatsApp Clinical Inbox & Analytics
- **Live Clinical Inbox**: Proves doctor can view patient conversation stream, message delivery receipts, and patient clinical summary.
- **Manual Reply & Dispatches**: Proves doctor can send manual replies, automatically pausing the bot and queueing outbound WhatsApp messages.
- **Acquisition Channel Analytics**: Proves `DoctorDashboard` renders volume and percentage breakdowns across WhatsApp, QR Standee, and Front-Desk.

Run the test suite with:
```bash
php artisan test --compact
```

---

## 🚀 Continuous Integration (GitHub Actions)

A production-ready GitHub Actions workflow is included at `.github/workflows/tests.yml`:
- Triggered automatically on every `push` and `pull_request` to `main`.
- Sets up PHP 8.4, Node.js 20, SQLite database, runs migrations, verifies code styling with Laravel Pint, and executes the complete 79-test PHPUnit test suite.

---

## 🎨 Code Style & Standards

The codebase adheres strictly to Laravel Pint conventions. Run Pint before finalizing changes:
```bash
vendor/bin/pint --format agent
```

---

## 💼 Bikrify Commercial Onboarding Playbook

When onboarding a new doctor or hospital to **Bikrify**:

1. **Step 1 — Provision Clinic via Super-Admin Portal (`/admin/tenants`)**:
   - Log in as Super Admin (`admin@bikrify.com`).
   - Enter Doctor Name, desired URL slug (e.g. `dr-farhana`), BMDC Reg. No., Doctor Email, and initial Password.
   - Configure Hospital Affiliation, Room Number, Visiting Hours, and Hotline. These verified credentials are permanently maintained by Super Admin to ensure doctor credential integrity.
   - Click **Create & Provision**. The tenant, doctor account, and default practice chamber are created atomically.
2. **Step 2 — Doctor Logs In to Ready-to-Use Studio**:
   - The doctor logs in at `/login` and accesses **Prescription Studio (`/app`)**.
   - Zero setup overhead: chamber details, qualifications, and specialty bundles are pre-configured.
3. **Step 3 — Print & Deliver the Door QR Standee**:
   - Open `/app/chambers/{id}/qr-standee` (or click **`📲 Door QR`** in the navigation bar).
   - Click **`🖨️ Print Chamber Door Poster`**.
   - Deliver the laminated poster or acrylic standee to place outside the doctor's chamber door for patient self-booking.
4. **Step 4 — Train Doctor on 1-Click Speed Prescribing**:
   - Demonstrate the **`⚡ 1-Click Full Profile`** bundle.
   - Demonstrate the **`💊 Quick Drug Kits`**.
   - Demonstrate **`🎙️ Voice Activate Studio`** in English and Bengali (`bn-BD`).
   - Demonstrate **`💬 Send WhatsApp`** with 7-day secure expiring digital prescription links.

---

## 📜 License & Security

- **License**: Distributed under the [MIT License](LICENSE).
- **Security Policy**: For vulnerability reporting and medical data protection standards, see [SECURITY.md](SECURITY.md).
- Developed with ❤️ by the **BikrifyCare Tech Team**.
