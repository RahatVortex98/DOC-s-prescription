# Bikrify Prescription Builder (Laravel 12 + Livewire 3)

## Quick Start for Collaborators

1. Clone & setup dependencies:
   ```bash
   git clone <repo_url> bikrify-prescriptions
   cd bikrify-prescriptions
   composer install
   npm install && npm run build
   cp .env.example .env
   php artisan key:generate
   ```

2. Setup database and seed medicines:
   ```bash
   touch database/database.sqlite # or configure MySQL/PostgreSQL in .env
   php artisan migrate --seed
   ```

3. Bulk import complete 42,000+ medicines:
   Place your DGDA-compliant full database dump at `database/data/medicines.csv` and run:
   ```bash
   php artisan medicines:import database/data/medicines.csv
   ```

4. Launch dev server:
   ```bash
   php artisan serve
   ```
