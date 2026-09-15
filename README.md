# Nexora Business OS — Backend

Laravel 12 REST API powering the Nexora frontend (`../frontend`). Sanctum
token auth, MySQL, one controller/policy per module, matching the exact
service files under `frontend/src/services/api/`.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+
- (Optional) Node not required here — this is backend-only.

## Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Create a MySQL database named `nexora` (or edit .env to match),
# then run migrations + seed roles/permissions/lookup data/demo admin:
php artisan migrate --seed

# Sanctum's personal access tokens table needs `php artisan install:api`
# if you're starting from a fresh Laravel install instead of this repo
# (already included here as a migration, so this is usually a no-op):
php artisan install:api

php artisan serve
# API now running at http://localhost:8000/api
```

## First login

The `UserSeeder` creates a Super Admin account:

- **Email:** `admin@nexora.com`
- **Password:** `password`

Change this immediately in a real deployment.

## Connecting the frontend

The frontend's `src/services/api/client.js` defaults to
`http://localhost:8000/api`, matching `php artisan serve`'s default port.
If you run the API on a different host/port, set `VITE_API_URL` in the
frontend's `.env`.

CORS is pre-configured for `http://localhost:5173` (Vite's default dev
port) via `CORS_ALLOWED_ORIGINS` in `.env`. Update this — and
`SANCTUM_STATEFUL_DOMAINS` — if you serve the frontend elsewhere.

## What's implemented

- **Auth**: register/login/logout/profile/forgot-password/reset-password via Sanctum tokens
- **RBAC**: 9 seeded roles (Super Admin → Viewer), permission-per-module, enforced via Policies + `role`/`permission` middleware aliases
- **CRM**: Leads (with scoring + convert-to-customer), Customers, Companies, Deals (drag-and-drop pipeline stage updates)
- **Work**: Tasks (list/board, checklist, comments), Projects (members, milestones, auto progress)
- **Calendar**: events with attendee RSVP
- **Finance**: Invoices (auto-recalculating totals from line items), Payments (auto-marks invoice Paid)
- **Files**: Documents with folders + version history, polymorphic Attachments on any CRM record
- **System**: Notifications (Laravel's built-in polymorphic table), Settings, Audit Logs, Reports catalog + Excel export scaffold, Dashboard/Analytics aggregation endpoints

## What's not yet built

- **Tests**: no PHPUnit/Pest test suite yet
- **Factories**: `database/factories/` is scaffolded but empty — needed for tests and for `DemoDataSeeder` to generate richer sample data
- **Queued jobs**: `app/Jobs/` is scaffolded but empty (e.g. invoice PDF generation, email notifications should eventually be queued rather than synchronous)
- **File storage in production**: currently assumes the `public` disk; swap to S3-compatible storage via `config/filesystems.php` for real deployments

## Verification note

This backend was written in an environment without PHP/Composer/Packagist
network access, so `composer install` / `php artisan migrate` have **not**
been executed against a real database. The code has been carefully
reviewed for Laravel 12 conventions, relationship correctness, and
consistency with the migrations — but a local `composer install && php
artisan migrate --seed` is the real test. Please report anything that
doesn't run cleanly.
