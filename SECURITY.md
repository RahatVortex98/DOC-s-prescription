# Security Policy & Medical Data Protection

## Overview

**Bikrify Prescription Studio** processes confidential patient records, clinical evaluations, and electronic prescriptions. Security and patient data privacy are core architectural requirements.

---

## Multi-Tenant Security & Data Isolation Guarantees

1. **Strict Tenant Scoping**: All database queries on patient records, appointments, prescriptions, and chambers enforce tenant isolation via the `BelongsToTenant` Eloquent global scope.
2. **Fail-Closed Access**: If a user is unauthenticated or has no tenant assigned (e.g. super-admin), tenant-scoped queries immediately resolve to `1 = 0`, returning zero rows.
3. **Locked Livewire Properties**: Critical IDs (`selected_patient_id`, `selected_chamber_id`, `current_appointment_id`) are immutable on the client side via Livewire `#[Locked]` attributes.
4. **Role-Based Privacy**: Front-desk and assistant users (`role = assistant`) are restricted from viewing clinical diagnosis or findings fields.
5. **Suspension Enforcement**: Suspended tenant accounts are immediately invalidated and logged out across all active sessions.

---

## Reporting a Vulnerability

If you discover a security vulnerability or patient data leakage concern within Bikrify Prescription Studio:

1. **Do not disclose publicly** or file a public GitHub issue.
2. Email your findings directly to **security@bikrify.com** or contact the lead engineering team.
3. Include:
   - Vulnerability description and impacted component(s)
   - Step-by-step reproduction instructions or proof-of-concept
   - Potential impact on tenant isolation or patient privacy
4. The Bikrify team will acknowledge your report within 24 hours and provide an update on remediation.

---

## Supported Versions

| Version | Supported |
| :--- | :--- |
| Laravel 13.x / Livewire 4.x / PHP 8.4 | :white_check_mark: Active |
