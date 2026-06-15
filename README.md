# Buzzvel 2026 Dev Team Test — Multi-Currency Payment API

A Laravel 12/13 + PHP 8.4 application that manages multi-currency payment requests for a
company with employees in different countries. Employees submit payment requests in
their local currency, the app automatically fetches the EUR exchange rate and the
converted EUR amount, and the finance team approves or rejects pending requests — via
both a **JSON REST API** and a **Blade + Tailwind web UI**.

## Features

- **Authentication**
  - REST API via [Laravel Sanctum](https://laravel.com/docs/sanctum) (register, login, logout, current user).
  - Web UI via standard session-based auth (register/login/logout pages).
- **Payment Requests**
  - Create a request in the user's local currency. The EUR → currency exchange rate
    is fetched automatically and **stored immutably** (rate, source, timestamp).
  - List requests with `status` filter (employees see their own; finance sees all).
  - View a single request (owner or finance only).
  - Approve / reject pending requests (finance role only).
- **Web UI** (Blade + Tailwind CSS): login/register, a dashboard with status filters,
  a "New Request" form, and a request detail page with approve/reject actions for
  the finance team.
- **Exchange Rate Integration** with [open.er-api.com](https://www.exchangerate-api.com/) (free, no API key required), cached for 5 minutes.
- **Automatic expiration**: a scheduled command marks `pending` requests older than 48h as `expired`.
- **Validation** for all inputs (amounts, currency codes, required fields).
- **Seeders** with 6 users (5 employees across different countries/currencies + 1 finance user) and sample payment requests.
- **Tests**: 41 feature/unit tests covering auth (API + web), payment requests (API + web), exchange rate service and the expiration command.

## Tech Stack

- PHP 8.4
- Laravel 13 (compatible with the Laravel 12 requirements of this test)
- Laravel Sanctum (API token authentication) + session-based auth for the web UI
- Blade templates + Tailwind CSS (via CDN — see [Frontend](#frontend) note)
- SQLite (default, zero-config)
- [Scramble](https://scramble.dedoc.co/) for interactive OpenAPI/Swagger-like API docs (`/docs/api`)

## Requirements

- PHP >= 8.2 (project uses 8.4)
- Composer
- SQLite extension for PHP (bundled by default)

## Setup

```bash
# 1. Clone and install dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite)
# database/database.sqlite already exists; if not, create it:
type nul > database/database.sqlite   # Windows
# touch database/database.sqlite      # macOS/Linux

php artisan migrate --seed

# 4. Run the app
php artisan serve
```

- **Web UI**: `http://127.0.0.1:8000` (login/register pages)
- **REST API**: `http://127.0.0.1:8000/api`
- **Docs API**: `http://127.0.0.1:8000/docs/api`

> **Windows note:** if PHP/Composer are not on your `PATH`, prefix commands with the
> full path to `php.exe` and `composer.bat` (e.g.
> `C:\PHP\php-8.4.x\php.exe artisan serve`).

## Frontend

The web UI (`/login`, `/register`, `/dashboard`, `/payment-requests/*`) is built with
Blade templates styled with **Tailwind CSS via the official CDN script**
(`cdn.tailwindcss.com`). This was a deliberate choice for this environment: it requires
**no Node.js/npm** (not available on this machine) while still giving the full Tailwind
v3 utility set and a clean, professional look (Inter font, consistent color system,
status badges, responsive tables/forms). For a production deployment you'd swap this
for the Vite + `@tailwindcss/vite` build already scaffolded in `package.json` —
no Blade markup changes would be needed beyond removing the CDN `<script>` tag in
`resources/views/partials/head.blade.php`.

The web UI and the JSON API share the same underlying `PaymentRequestService`
(`app/Services/PaymentRequestService.php`), so business rules (immutable exchange
rate, 48h expiration, finance-only approval) are enforced identically for both.

### Exchange Rate Provider

The integration uses [open.er-api.com](https://open.er-api.com) (free, no API key).
Configurable via `.env`:

```
EXCHANGE_RATE_API_URL=https://open.er-api.com/v6/latest
EXCHANGE_RATE_CACHE_TTL=300
```

> If your PHP install reports `cURL error 60: SSL certificate ... unable to get local
> issuer certificate`, download a CA bundle (e.g. https://curl.se/ca/cacert.pem) and
> set `curl.cainfo` / `openssl.cafile` in `php.ini` to its path.

## Seeded Users

All seeded users share the password `password`.

| Name              | Email                          | Country        | Currency | Role     |
|-------------------|--------------------------------|----------------|----------|----------|
| Joao Silva        | joao.silva@buzzvel.com         | Portugal       | EUR      | employee |
| Maria Costa       | maria.costa@buzzvel.com        | Brazil         | BRL      | employee |
| John Smith        | john.smith@buzzvel.com         | United Kingdom | GBP      | employee |
| Carlos Rodriguez  | carlos.rodriguez@buzzvel.com   | Mexico         | MXN      | employee |
| Yuki Tanaka       | yuki.tanaka@buzzvel.com        | Japan          | JPY      | employee |
| Anna Mueller      | anna.mueller@buzzvel.com       | Germany        | EUR      | finance  |

Each employee also has one pending, one approved, and (for the first employee) one
expired payment request seeded for convenience.

You can sign in with any of these accounts on the **web UI** (`/login`) or use them
to obtain a Sanctum token via `POST /api/login`. New accounts (employee or finance)
can also be created from `/register`.

## Running Tests

```bash
php artisan test
```

The test suite uses an in-memory SQLite database and `Http::fake()` for the exchange
rate provider — no network access or API keys required. It covers both the JSON API
and the Blade web UI (authentication, payment request CRUD, authorization, validation,
exchange rate service and the expiration command).

## Scheduled Task — Expiring Pending Requests

A pending payment request older than 48 hours is automatically marked as `expired`
by the `payments:expire-pending` command, scheduled to run hourly.

```bash
# Run once manually
php artisan payments:expire-pending

# Run the scheduler loop (keeps running, dispatches due tasks)
php artisan schedule:work
```

## API Documentation

- **Interactive (Swagger-like)**: with the app running, open `http://127.0.0.1:8000/docs/api`
  for a [Scramble](https://scramble.dedoc.co/)-generated, Stoplight Elements UI built
  from the actual routes/FormRequests/Resources. It includes "Try it out" — register
  or log in via the **Authentication** endpoints to get a token, click **Authorize**
  in the top right, paste it as `Bearer <token>`, and call the protected
  **Payment Requests** endpoints directly from the browser. The raw OpenAPI 3.1 spec
  is at `/docs/api.json`. A link to this page ("API Docs") is also in the web UI navbar.
- **Written**: request/response examples, validation rules and error formats are also
  documented in [`docs/API.md`](docs/API.md).

## Project Structure Highlights

```
app/
├── Console/Commands/ExpirePendingPaymentRequests.php   # 48h expiration job
├── Exceptions/{ExchangeRateException,PaymentRequestReviewException}.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/{AuthController,PaymentRequestController}.php      # JSON API
│   │   └── Web/{AuthController,PaymentRequestController}.php      # Blade UI
│   ├── Requests/Auth/{RegisterRequest,LoginRequest}.php
│   ├── Requests/PaymentRequest/{StorePaymentRequestRequest,IndexPaymentRequestRequest}.php
│   └── Resources/PaymentRequestResource.php
├── Models/{User,PaymentRequest}.php
├── Policies/PaymentRequestPolicy.php
└── Services/{ExchangeRateService,PaymentRequestService}.php        # shared business logic

config/currencies.php   # Supported currencies + exchange rate provider config
database/
├── factories/{UserFactory,PaymentRequestFactory}.php
├── migrations/
└── seeders/{UserSeeder,PaymentRequestSeeder}.php
docs/API.md
resources/views/
├── layouts/{app,guest}.blade.php
├── partials/head.blade.php
├── components/status-badge.blade.php
├── auth/{login,register}.blade.php
└── payment-requests/{index,create,show}.blade.php
tests/
├── Feature/Auth/AuthenticationTest.php          # API auth
├── Feature/PaymentRequests/PaymentRequestTest.php  # API payment requests
├── Feature/Web/{AuthenticationTest,PaymentRequestTest}.php  # web UI
└── Unit/{Services/ExchangeRateServiceTest,Console/ExpirePendingPaymentRequestsTest}.php
```

## Design Decisions

- **Sanctum over Passport**: Sanctum is the modern, lightweight Laravel option for SPA/API
  token authentication and is sufficient for this stateless API.
- **Immutable exchange rate**: the rate, its source URL, and the fetch timestamp are
  stored on the `payment_requests` row at creation time and never recalculated.
- **Authorization**: a `PaymentRequestPolicy` restricts viewing to the owner or finance
  users; approve/reject is handled by `PaymentRequestService::review()`, which throws a
  `PaymentRequestReviewException` carrying the right HTTP status (403/409) — translated
  to JSON by the API controller and to flash messages by the web controller.
- **Shared service layer**: `PaymentRequestService` (create + review) is used by both
  the API and web controllers, so the JSON API and the Blade UI enforce identical
  business rules.
- **Currency validation**: a configurable allow-list in `config/currencies.php` keeps
  validation simple while remaining easy to extend.
- **Tailwind via CDN**: see [Frontend](#frontend) — no Node.js required to run or view
  the UI in this environment.
