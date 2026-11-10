# BikrifyCare - Prescription Studio (DOC-s-prescription)

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Tests](https://img.shields.io/badge/PHPUnit-44%20Passing-brightgreen?style=for-the-badge&logo=github-actions&logoColor=white)](#automated-testing)
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

### 7. 👥 Patient Queue & "No-Receptionist" Self-Booking Ecosystem

#### Case A: Doctor HAS a Receptionist (`/app/appointments`)
- **Receptionist Queue Desk**: Receptionists enter Patient Name, Age, Gender, Phone, and preferred Time Slot.
- **Automatic Concurrency-Safe Serial Generation**: Issues atomic serial numbers (`#1`, `#2`, `#3`...) using `DB::transaction()` with `lockForUpdate()` to prevent race conditions.
- **WhatsApp Confirmation**: 1-click button to send appointment confirmations directly to the patient's WhatsApp.
- **Live Sync with Doctor Studio**: The doctor sees a **"LIVE QUEUE"** banner at the top of the Prescription Studio. Clicking **"Call Patient #1"** automatically loads their details into the prescription. Saving the prescription automatically marks the queue token as `Completed`.

#### Case B: Doctor HAS NO Receptionist (Solo Practice Solution)
- **Public Patient Self-Booking Portal (`/{tenant:slug}`)**:
  - Example: `https://prescriptions.bikrify.com/dr-mahbubur`
  - Mobile-first, privacy-protected portal where patients book from their phone.
  - Required consent checkbox (*"I agree to the processing of my personal data"*).
  - Validation rejecting past dates (`after_or_equal:today`).
  - Strict rate limiting: 30 requests/min and maximum 5 bookings per 60 seconds per IP.
  - Instantly generates an electronic **Serial Token Card** with live waiting counter.
- **Printable Chamber Door QR Standee Poster (`/app/chambers/{chamber}/qr-standee`)**:
  - Professional A4 standee poster featuring Doctor's credentials, hospital name, room number, visiting hours, and a **large scannable QR Code** linking directly to the tenant's booking portal.
  - Bilingual instructions: *"Scan with Phone Camera to Get Serial Token"* / *"আপনার মোবাইল ক্যামেরা দিয়ে স্ক্যান করে সিরিয়াল টোকেন নিন"*.
  - Ready to print, laminate, and place outside the chamber door.

---

### 8. 🛡️ Clinical Safety, Drug Allergies & Prescription Voiding
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
| **Testing** | PHPUnit | `^12.5` | 43 comprehensive feature, safety & isolation tests |
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

### 2. Run Database Migrations & Seeders
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
| `/app/dashboard` | `doctor.dashboard` | `auth, tenant.active` | Practice analytics dashboard. |
| `/app/prescriptions` | `prescription.list` | `auth, tenant.active` | Prescription records, search, voiding, and reprint. |
| `/app/appointments` | `appointment.manager` | `auth, tenant.active` | Patient queue desk & appointment booking. |
| `/app/audit-logs` | `audit.logs` | `auth, tenant.active` | Security & clinical event audit log viewer. |
| `/app/chambers/{id}/qr-standee` | `chamber.qr_standee` | `auth, tenant.active` | Printable A4 chamber door QR standee poster. |
| `/app/prescriptions/{id}/print` | `prescription.print` | `auth, tenant.active` | A4 prescription print & letterhead view. |
| `/app/prescriptions/{id}/pdf` | `prescription.pdf` | `auth, tenant.active` | DomPDF download stream. |
| `/app/prescriptions/{id}/investigations`| `prescription.investigations` | `auth, tenant.active`| Lab test requisition slip. |
| `/rx/{public_id}` | `prescription.public_share` | `signed` | Secure 7-day expiring patient WhatsApp prescription link. |
| `/admin/tenants` | `admin.tenants` | `auth, role.super_admin` | Super-admin tenant provisioning & profile governance. |
| `/{tenant:slug}` | `appointment.public_book`| `throttle:30,1` | Public patient self-booking page (e.g. `/dr-mahbubur`). |

---

## 🧪 Automated Testing

This application includes a PHPUnit test suite with **44 passing tests** and **201 assertions** verifying:
- **Tenant Isolation**: Proves Tenant B cannot query, see, or search Tenant A's patients, prescriptions, or appointments.
- **Route Authorization**: Proves cross-tenant access to `/app/prescriptions/{id}/print`, `/pdf`, and `/investigations` returns `404 Not Found`.
- **Livewire ID Protection**: Proves passing another tenant's ID to Livewire actions returns `404 Not Found`.
- **Guest Protection**: Proves all unauthenticated requests to `/app/*` redirect to `/login`.
- **Suspension Enforcement**: Proves suspended tenants cannot access the studio or accept public bookings.
- **Public Booking Isolation**: Proves bookings at `/{tenant:slug}` write strictly into that tenant's records.
- **Rate Limiting**: Proves public booking enforces IP-based rate limiting against spam.
- **Assistant Role Security**: Proves front-desk assistants cannot see clinical diagnosis or examination findings.
- **Prescription Builder & Dropdowns**: Proves 1-click specialty bundles, drug kits, vitals calculation, and medicine search.
- **DGDA CSV Bulk Import**: Proves streaming medicine database import works idempotently without duplicate records.
- **Patient Disambiguation**: Proves family members sharing a phone receive separate, distinct medical records.
- **Drug Allergies Persistence**: Proves patient allergies persist and render visibly on print and PDFs.
- **Soft Deletes Protection**: Proves soft-deleted appointments and prescriptions remain preserved for audit integrity.
- **Prescription Voiding**: Proves voided prescriptions display VOID watermarks and cannot be duplicated.
- **7-Day Signed Links**: Proves expiring signed URLs allow patient access for 7 days while rejecting tampered requests.
- **Audit Logging**: Proves prescription creation, printing, voiding, and deletion actions log actor IP and timestamps.

Run the test suite with:
```bash
php artisan test --compact
```

---

## 🚀 Continuous Integration (GitHub Actions)

A production-ready GitHub Actions workflow is included at `.github/workflows/tests.yml`:
- Triggered automatically on every `push` and `pull_request` to `main`.
- Sets up PHP 8.4, Node.js 20, SQLite database, runs migrations, verifies code styling with Laravel Pint, and executes the complete 44-test PHPUnit test suite.

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
