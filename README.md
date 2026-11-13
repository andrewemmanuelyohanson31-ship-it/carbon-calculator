# CarbonCheck — Household Carbon Footprint Calculator

A Laravel 12 web app that estimates a household's yearly CO₂ emissions, inspired by the
[US EPA Carbon Footprint Calculator](https://www.epa.gov/ghgemissions/carbon-footprint-calculator).

**Flow:** Landing → choose categories (household energy, transport, shopping & waste) → fill in details
→ results → recommendations → start over. Past results are listed on the History page.

## Requirements

- PHP 8.2+ and Composer
- MySQL / MariaDB (e.g. XAMPP — create the database in phpMyAdmin)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

1. In phpMyAdmin create a database called `carbon_calculator` (collation `utf8mb4_unicode_ci`).
2. Check the `DB_*` values in `.env` (XAMPP default: user `root`, empty password).
3. Run:

```bash
php artisan migrate
php artisan serve
```

Open http://127.0.0.1:8000.

## Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database, so they never touch your MySQL data.

## How it works

| Part | File |
|------|------|
| Emission factors + formulas + recommendations | `app/Services/CarbonCalculator.php` |
| Pages and validation | `app/Http/Controllers/CalculatorController.php` |
| Routes | `routes/web.php` |
| Database table | `database/migrations/*_create_calculations_table.php` |
| Views / styling | `resources/views/`, `public/css/app.css` |

Emission factors are rounded public averages (IEA grid average, UK DEFRA 2023, US EPA waste data).
Change the constants in `CarbonCalculator.php` to match your country.

## Security notes

- No login is needed. Each browser gets a random ID in an **encrypted, HTTP-only cookie**; history and results
  are only visible to that browser (anyone else gets a 404). Clearing cookies starts a fresh history.
- Results use UUIDs, so URLs can't be guessed.
- All input is validated server-side with min/max limits; Blade escapes all output; forms use CSRF tokens.
- The calculation form is rate-limited (20 submissions per minute).
- Security headers (CSP, X-Frame-Options, nosniff) are set in `app/Http/Middleware/SecurityHeaders.php`.
- `.env` (with `APP_KEY` and DB password) is git-ignored — never commit it. For production set
  `APP_ENV=production` and `APP_DEBUG=false`.
