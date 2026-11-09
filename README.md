# Bikrify Prescription Studio (DOC-s-prescription)

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Tests](https://img.shields.io/badge/PHPUnit-33%20Passing-brightgreen?style=for-the-badge&logo=github-actions&logoColor=white)](#automated-testing)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

**Bikrify Prescription Studio** is a commercial-grade, multi-tenant clinical Prescribing Platform and Patient Queue Management SaaS built specifically for doctors, hospital consultants, polyclinics, and receptionists in Bangladesh and global private practice.

---

## 🌟 Executive Feature Highlights

### 1. 🏢 Multi-Tenant SaaS Architecture & Doctor Isolation
One application, one shared database, with complete isolation across independent doctor clinics:
- **`tenant_id` Scoping**: All chambers, patients, prescriptions, and appointments are strictly scoped by `tenant_id`.
- **Global Tenant Scope (`BelongsToTenant`)**: Automatically isolates every Eloquent query to the authenticated doctor's clinic. Unauthorized cross-tenant queries resolve to `1 = 0` (zero rows) and trigger `404 Not Found`.
- **Three-Tier Role System**:
  - `super_admin`: Provisions new clinics, toggles trial/active/suspended states, zero access to private medical records.
  - `doctor`: Complete clinical prescribing workspace, past patient history, diagnostics, and print/PDF streaming.
  - `assistant`: Front-desk patient intake and queue token management. Clinical findings and diagnoses are automatically masked/hidden from assistant view.
- **Tenant Status Enforcement**: Instantly blocks suspended clinic accounts at the middleware level, invalidating active sessions.

---

### 2. 🏥 Multi-Hospital Chamber Seating & Profile Customization
Doctors often practice across multiple private hospitals and chambers throughout the week:
- **1-Click Hospital Switcher**: Instantly toggle between locations (e.g. *Square Hospital*, *Popular Diagnostic Center*, *Bikrify Banani Chamber*).
- **Comprehensive Profile Setup**:
  - Doctor Name, Medical Qualifications (`MBBS, FCPS, MD, BCS (Health)`), and BMDC Reg. No.
  - Institutional Designation & Department (e.g. *Professor & Chief Consultant, Department of Nephrology*)
  - Hospital Affiliation, Room/Chamber Number, and Floor
  - Visiting Hours (e.g. *5:00 PM - 9:00 PM, Sat-Wed*)
  - Serial Hotline Number, Personal Phone, and a **Direct WhatsApp Hotline** (`https://wa.me/...`)
- **Onboarding Modal (`⚙️ Setup`)**: Bikrify onboarding specialists or clinic staff can configure new chamber profiles in seconds.

---

### 3. 🎯 Specialty-Driven Dynamic Clinical Workspaces
When a doctor selects their active specialty, the clinical workspace tailors Chief Complaints, Examination (O/E), Diagnoses, Laboratory Investigations, and Advice chips:
- **Nephrology / CKD Specialist**: Full clinical protocols for Chronic Kidney Disease (Stage 1–5, ESRD), Hypertensive Nephrosclerosis, Diabetic Nephropathy, Glomerulonephritis, S. Creatinine, eGFR, Spot ACR, KUB USG, low-protein diets, and fluid limits.
- **Cardiology**: IHD, Angina, CCF, NSTEMI, Atrial Fibrillation, ECG, Echocardiography, Troponin-I, Lipid profiles, and cardiac advice.
- **General Medicine / Internal Medicine**: Acute viral syndromes, Typhoid/Enteric fever, UTI, Dengue NS1 workups, pneumonia, and fever management.
- **Gastroenterology**: GERD, PUD, IBS, Endoscopy, H. Pylori, USG of whole abdomen, and dietary acid reduction protocols.
- **Diabetology / Endocrinology**: Type 2 Diabetes Mellitus, Diabetic Neuropathy, HbA1c, microalbuminuria, lipid profiles, diabetic foot care, and blood sugar tracking.

---

### 4. ⚡ Less-Input Automation (Near-Zero Doctor Typing)
Doctors should spend consultation time speaking with patients, not typing on keyboards:
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

### 5. 🎙️ Browser-Native Voice Dictation (Speech-to-Text)
- **Zero Paid API Cost**: Uses browser-native Web Speech API (`SpeechRecognition`) in Chrome, Edge, and Safari.
- **Bilingual Dictation**: Toggle between **English (Medical Terminology)** and **বাংলা (Bengali - `bn-BD`)**.
- **Dedicated Voice Mics**: Available on Chief Complaints, Examination (O/E), Diagnosis, Investigations, and Special Advice. Clicking pulses red (`🎙️ Listening...`) and transcribes speech into the active field.

---

### 6. 👥 Patient Queue & "No-Receptionist" Self-Booking Ecosystem

#### Case A: Doctor HAS a Receptionist (`/app/appointments`)
- **Receptionist Queue Desk**: Receptionists enter Patient Name, Age, Gender, Phone, and preferred Time Slot.
- **Automatic Concurrency-Safe Serial Generation**: Issues atomic serial numbers (`#1`, `#2`, `#3`...) using `DB::transaction()` with `lockForUpdate()` to prevent race conditions.
- **WhatsApp Confirmation**: 1-click button to send appointment confirmations directly to the patient's WhatsApp.
- **Live Sync with Doctor Studio**: The doctor sees a **"LIVE QUEUE"** banner at the top of the Prescription Studio. Clicking **"Call Patient #1"** automatically loads their details into the prescription. Saving the prescription automatically marks the queue token as `Completed`.

#### Case B: Doctor HAS NO Receptionist (Solo Practice Solution)
- **Public Patient Self-Booking Portal (`/{tenant:slug}`)**:
  - Example: `http://localhost:8000/dr-mahbubur`
  - Mobile-first, privacy-protected portal where patients book from their phone.
  - Required consent checkbox (*"I agree to the processing of my personal data"*).
  - Validation rejecting past dates (`after_or_equal:today`).
  - Strict rate limiting: 30 requests/min and maximum 5 bookings per 60 seconds per IP.
  - Instantly generates an electronic **Serial Token Card** with live waiting counter.
- **Printable Chamber Door QR Standee Poster (`/app/chambers/{chamber}/qr-standee`)**:
  - Professional A4 standee poster featuring the Doctor's credentials, hospital name, room number, visiting hours, and a **large scannable QR Code** linking directly to the tenant's booking portal.
  - Bilingual instructions: *"Scan with Phone Camera to Get Serial Token"* / *"আপনার মোবাইল ক্যামেরা দিয়ে স্ক্যান করে সিরিয়াল টোকেন নিন"*.
  - Ready to print, laminate, and place outside the chamber door.

---

### 7. 🔍 Old Patient Lookup & Past Prescription History
- **Live Search**: Search by Phone Number (`017...`), Patient Name, or Patient ID (`PID-XXXX`).
- **History Drawer**: When an existing patient is loaded, click **"📋 Past Visits"** to view previous consultation dates, diagnoses, complaints, and prescribed medicines.
- **1-Click "Repeat Previous Rx"**: Copy all medicines and dosages from their previous visit into the builder with one click—ideal for chronic patients (CKD, hypertension, diabetes) coming for refills.

---

### 8. 💊 High-Performance Medicine Database & Fast Dropdowns
- **Comprehensive Bangladesh DGDA Dataset**: Pre-seeded with complete brand variant families:
  - **Napa**: `Napa 500mg`, `Napa Extra 500+65mg`, `Napa Extend 665mg`, `Napa One 1000mg`, `Napa Rapid`, `Napa Suspension`, `Napa Paediatric Drop`, `Napa Suppository`, `Napa IV Infusion`.
  - **Ace**: `Ace 500mg`, `Ace Plus`, `Ace Power 1000mg`, `Ace Suspension`.
  - **CKD / Nephrology**: `Ketosteril`, `Ulsicral`, `Eprex / Epotin 4000 & 10000 IU`, `Sodibicarb`, `Febux`, `Rocaltrol`, `Torsid`, `Lasix`, `Renvela`, `Calcichew D`, `Amlopin`, `Bizoran`, `Angilock`, `Concor`, `Iron Sucrose`.
- **Tokenized Search**: Typing `napa extra`, `napa`, or `paracetamol` matches all corresponding variants with prefix prioritization.
- **100% Manual Flexibility**: Doctors can also type any custom/new medicine name freely.
- **Fast Preset Datalists**:
  - **Form**: `Tab.`, `Cap.`, `Inj.`, `Syr.`, `Drop`, `Oint.`, `Inhaler`, `Supp.`
  - **Timing**: `1+0+0` (সকালে), `0+1+0` (দুপুরে), `0+0+1` (রাতে), `1+0+1` (সকাল + রাত), `1+1+1` (৩ বেলা), `1+1+1+1` (৪ বেলা), `1/2+0+1/2`, `1 spoon 3 times`, `2 puffs twice daily`, `As needed (PRN)`.
  - **Duration**: `3 days`, `5 days`, `7 days`, `10 days`, `14 days`, `1 month`, `2 months`, `3 months`, `Continue (চলবে)`.
  - **Instructions**: `After meal (খাবার পরে)`, `Before meal (খাবার আগে)`, `With meal (খাবারের সাথে)`, `Empty stomach (খালি পেটে)`, `At bedtime (ঘুমানোর আগে)`, `Before breakfast`, `After breakfast`, `As directed`.

---

### 9. 🖨️ A4 Prescription Printing & PDF Streaming
- **Dual-Mode Prescription Output**:
  - **Full Print Mode**: Renders complete doctor letterhead, institutional credentials, hospital room number, patient details, clinical findings, medicines, and footer.
  - **Pre-printed Pad Mode (Letterhead Spacer)**: Toggle on if the doctor writes on a pre-printed hospital pad. Hides header and reserves an adjustable 40mm–70mm top margin.
- **A4 PDF Export**: Instant server-side PDF generation via DomPDF.
- **Diagnostic Lab Test Requisition Slip (`/app/prescriptions/{id}/investigations`)**: Separates lab tests onto a dedicated investigation requisition slip with clinical indications.
- **Direct WhatsApp Sharing**: Generates a pre-formatted WhatsApp message with patient name, prescription number, and a link to their digital prescription.

---

### 10. 📊 Practice Analytics Dashboard (`/app/dashboard`)
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
| **Testing** | PHPUnit | `^12.5` | 33 comprehensive feature/isolation tests |
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

## 🔑 Default Seeded Accounts

Log in at **`http://localhost:8000/login`** using any of the following credentials:

| Role | Email | Password | Access & Purpose |
| :--- | :--- | :--- | :--- |
| **Doctor** | `doctor@bikrify.com` | `password` | **Clinical Studio**: Full prescribing workspace, patient history, and analytics. |
| **Assistant** | `assistant@bikrify.com` | `password` | **Front-Desk**: Queue booking & status updates (clinical findings/diagnosis masked). |
| **Super Admin** | `admin@bikrify.com` | `password` | **Bikrify Portal**: Provision doctor clinics at `http://localhost:8000/admin/tenants`. |

---

## 🔗 Key Application Routes

| Route | Name | Middleware | Description |
| :--- | :--- | :--- | :--- |
| `/login` | `login` | `guest` | Livewire authentication portal. |
| `/app` | `prescription.builder` | `auth, tenant.active` | Main Prescription Studio workspace. |
| `/app/dashboard` | `doctor.dashboard` | `auth, tenant.active` | Practice analytics dashboard. |
| `/app/prescriptions` | `prescription.list` | `auth, tenant.active` | Prescription records, search, and reprint. |
| `/app/appointments` | `appointment.manager` | `auth, tenant.active` | Patient queue desk & appointment booking. |
| `/app/chambers/{id}/qr-standee` | `chamber.qr_standee` | `auth, tenant.active` | Printable A4 chamber door QR standee poster. |
| `/app/prescriptions/{id}/print` | `prescription.print` | `auth, tenant.active` | A4 prescription print & letterhead view. |
| `/app/prescriptions/{id}/pdf` | `prescription.pdf` | `auth, tenant.active` | DomPDF download stream. |
| `/app/prescriptions/{id}/investigations`| `prescription.investigations` | `auth, tenant.active`| Lab test requisition slip. |
| `/admin/tenants` | `admin.tenants` | `auth, role.super_admin` | Super-admin tenant provisioning portal. |
| `/{tenant:slug}` | `appointment.public_book`| `throttle:30,1` | Public patient self-booking page (e.g. `/dr-mahbubur`). |

---

## 🧪 Automated Testing

This application includes a PHPUnit test suite with **33 passing tests** and **154 assertions** verifying:
- **Tenant Isolation**: Proves Tenant B cannot query, see, or search Tenant A's patients, prescriptions, or appointments.
- **Route Authorization**: Proves cross-tenant access to `/app/prescriptions/{id}/print`, `/pdf`, and `/investigations` returns `404 Not Found`.
- **Livewire ID Protection**: Proves passing another tenant's ID to Livewire actions returns `404 Not Found`.
- **Guest Protection**: Proves all unauthenticated requests to `/app/*` redirect to `/login`.
- **Suspension Enforcement**: Proves suspended tenants cannot access the studio or accept public bookings.
- **Public Booking Isolation**: Proves bookings at `/{tenant:slug}` write strictly into that tenant's records.
- **Rate Limiting**: Proves public booking enforces IP-based rate limiting against spam.
- **Assistant Role Security**: Proves front-desk assistants cannot see clinical diagnosis or examination findings.
- **Prescription Builder & Dropdowns**: Proves 1-click specialty bundles, drug kits, vitals calculation, and medicine search.
- **DGDA CSV Bulk Import**: Proves streaming medicine database import works idempotently.

Run the test suite with:
```bash
php artisan test --compact
```

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
   - Click **Create & Provision**. The tenant and doctor account are created in an atomic transaction.
2. **Step 2 — Configure Practice Chambers**:
   - The doctor logs in at `/login` and accesses **Prescription Studio (`/app`)**.
   - Click **`+ Hospital`** or **`⚙️ Setup`** to add additional private hospitals, visiting hours, room numbers, and WhatsApp numbers.
3. **Step 3 — Print & Deliver the Door QR Standee**:
   - Open `/app/chambers/{id}/qr-standee` (or click **`📲 Door QR`** in the navigation bar).
   - Click **`🖨️ Print Chamber Door Poster`**.
   - Deliver the laminated poster or acrylic standee to place outside the doctor's chamber door.
4. **Step 4 — Train Doctor on 1-Click Speed Prescribing**:
   - Demonstrate the **`⚡ 1-Click Full Profile`** bundle.
   - Demonstrate the **`💊 Quick Drug Kits`**.
   - Demonstrate **`🎙️ Voice Dictation`** in English and Bengali.
   - Demonstrate **`💬 Send WhatsApp`** to deliver digital prescriptions directly to the patient's phone.

---

## 📜 License & Security

- **License**: Distributed under the [MIT License](LICENSE).
- **Security Policy**: For vulnerability reporting and medical data protection standards, see [SECURITY.md](SECURITY.md).
- Developed with ❤️ by the **Bikrify Health Tech Team**.
