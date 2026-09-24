<div align="center">
  <img src="public/favicon.svg" alt="Idir Logo" width="120" />

  # Idir Platform (እድር ፕላትፎርም)
  
  **The Open-Source Digital Infrastructure for Ethiopian Social Insurance**

  [![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![Filament](https://img.shields.io/badge/Filament-5.x-FBBF24?style=for-the-badge&logo=laravel&logoColor=black)](https://filamentphp.com)
  [![Tailwind CSS](https://img.shields.io/badge/Tailwind_4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
  [![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)
  [![Test Coverage](https://img.shields.io/badge/Coverage-100%25-success.svg?style=for-the-badge)](#16-testing)
</div>

---

## 📑 Table of Contents

1. [Project Overview](#1-project-overview)
2. [Problem Statement](#2-problem-statement)
3. [Why This Project Exists](#3-why-this-project-exists)
4. [Key Features](#4-key-features)
5. [User Roles](#5-user-roles)
6. [Screenshots](#6-screenshots)
7. [Architecture](#7-architecture)
8. [Tech Stack](#8-tech-stack)
9. [Database Design](#9-database-design)
10. [API Overview](#10-api-overview)
11. [Getting Started](#11-getting-started)
12. [Installation](#12-installation)
13. [Environment Variables](#13-environment-variables)
14. [Running Locally](#14-running-locally)
15. [Development Workflow](#15-development-workflow)
16. [Testing](#16-testing)
17. [Deployment](#17-deployment)
18. [CI/CD](#18-cicd)
19. [Security](#19-security)
20. [Contributing](#20-contributing)
21. [Roadmap](#21-roadmap)
22. [FAQ](#22-faq)
23. [License](#23-license)
24. [Acknowledgements](#24-acknowledgements)

---

## 1. Project Overview

**Idir Platform (እድር)** is a robust, multi-tenant digital management system built for traditional Ethiopian community social insurance associations. It bridges time-tested societal trust models with modern fintech APIs (Chapa/Telebirr) and national digital infrastructure (Fayda OIDC).

## 2. Problem Statement

Historically, Idirs rely on fragmented, physical ledgers and cash-in-hand collections. This analog approach causes:
- **Opaque Governance:** Difficulty in auditing funds and tracking exact member contributions.
- **Geographic Friction:** Members in the diaspora or different cities struggle to maintain good standing.
- **Inefficiency:** Claim processing for funerals/emergencies requires physical meetings and cash withdrawals.

## 3. Why This Project Exists

This project exists to digitize and scale the Ethiopian social safety net. By providing a free, open-source, mathematically immutable ledger system, we empower community leaders to manage millions of Birr transparently, while allowing members to pay dues instantly via digital channels.

## 4. Key Features

- 🏢 **Multi-Tenancy:** Single-database architecture hosting isolated Idir associations.
- 💳 **Digital Payments:** Automated webhook-driven ledger entries via Chapa and Telebirr.
- 🔐 **Fayda Digital ID:** Secure eSignet OIDC integration for verified Ethiopian identities.
- 📊 **Immutable Ledgers:** Double-entry accounting where negative corrections replace dangerous deletions.
- 📝 **Multi-Signature Claims:** Committee threshold voting ($N$-approvals) required for fund disbursement.
- 💬 **Omnichannel Notifications:** AfroMessage SMS and Telegram Bot Webhook integrations.
- ⚡ **Realtime Updates:** Laravel Reverb powered WebSocket ledger reflections.

## 5. User Roles

| Role | Capabilities | Workspace |
| --- | --- | --- |
| **Platform Owner** | Global oversight, system logs, approving new Idir tenants. Cannot view individual tenant ledgers. | `/admin` |
| **Committee (Chair/Treasurer)** | View member ledgers, process claims, record cash payments, update Idir settings. | `/committee` |
| **Member** | View personal contributions, file claims, vote on general assembly items, pay digitally. | `/member` |
| **Guest** | Public phone lookup, self-service Idir registration requests, OIDC login. | `/` (Root) |

## 6. Screenshots

<details>
<summary><b>Click to expand Visual Tour</b></summary>

### 1. Landing & Authentication
![Landing Page](docs/screenshots/selfservice_01_landing_page.png)
*Strict 3-Color Minimalist (Black, Blue, White) aesthetic with mathematically precise SVG topography.*

### 2. Member Portal
![Member Dashboard](docs/screenshots/07_member_dashboard.png)
*Real-time contribution tracking and ledger history.*

### 3. Committee Ledger Administration
![Committee Panel](docs/screenshots/03_contribution_ledger.png)
*Filament v5 multi-tenant ledger management with strict data isolation.*

</details>

## 7. Architecture

The platform follows a **Modular Monolith** pattern:
- **Core Engine:** Laravel 13.x powers the MVC flow, Eloquent ORM, and dependency injection.
- **Tenancy:** Single DB multi-tenancy enforced through `IdirScope` and `BelongsTo(Idir::class)` relationships.
- **Admin GUI:** Filament v5 provides rapid, livewire-driven data tables and forms for Committees.
- **Public Portal:** Custom Blade + Tailwind v4 + AlpineJS handles the lightweight, high-performance member web interface.
- **Asynchronous Processing:** Redis/Horizon manages Chapa webhooks, SMS dispatch, and OIDC token renewals.

## 8. Tech Stack

- **Framework:** Laravel 13.x (PHP 8.3+)
- **Admin Panel:** Filament v5.x
- **Frontend:** Tailwind CSS v4.x, Alpine.js, Blade Components
- **Database:** PostgreSQL / MySQL (Tested on SQLite for CI)
- **Queues / Cache:** Redis + Laravel Horizon
- **Realtime:** Laravel Reverb (WebSockets)
- **Testing:** PHPUnit, Playwright (E2E)

## 9. Database Design

Key Entities & Relationships:
- `User` `hasMany` `Member`
- `Idir` `hasMany` `Member`, `Contribution`, `Claim`
- `Member` `belongsTo` `Idir` & `User`
- `Claim` `hasMany` `ClaimApproval` (Multi-signature pattern)

*Constraint Rule:* All financial entities (`Contribution`, `Claim`) are strictly bound to an `idir_id` foreign key, secured by global scopes.

## 10. API Overview

External integrations interact via protected Sanctum and Webhook routes:

- **`POST /api/auth/token`**: Mobile app authentication (Rate Limited: 30/min).
- **`GET /api/member/*`**: Authenticated JSON endpoints for profile, claims, and ledgers.
- **`POST /api/chapa/webhook`**: Cryptographically verified (`HMAC-SHA256`) asynchronous payment processor.
- **`POST /api/telegram/webhook`**: Telegram Bot callback secured by `X-Telegram-Bot-Api-Secret-Token`.

## 11. Getting Started

Before you begin, ensure you have:
- PHP 8.3+
- Composer
- Node.js & NPM
- Redis (for Horizon/Reverb)
- PostgreSQL / MySQL / SQLite

## 12. Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Bedru-Mekiyu/Idir.git
   cd idir
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JavaScript dependencies:
   ```bash
   npm install
   ```
4. Copy the environment file and generate the app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Migrate the database and seed Ethiopian test data:
   ```bash
   php artisan migrate --seed
   ```

## 13. Environment Variables

Essential variables required for booting:

```ini
APP_NAME="Idir Platform"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite # Or pgsql/mysql

# Queue & Cache
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Fintech & Messaging (Optional for Local)
CHAPA_SECRET_KEY=your_secret
CHAPA_WEBHOOK_SECRET=your_webhook_secret
AFROMESSAGE_TOKEN=your_token
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_WEBHOOK_SECRET=your_secret

# Laravel Reverb
REVERB_APP_ID=idir-local
REVERB_APP_KEY=idir-key
REVERB_APP_SECRET=idir-secret
```

## 14. Running Locally

You will need four terminal sessions to run the complete stack:

```bash
# 1. HTTP Server
php artisan serve

# 2. Asset Compilation (Tailwind v4 / Vite)
npm run dev

# 3. Queue Worker
php artisan horizon

# 4. WebSocket Server
php artisan reverb:start
```

## 15. Development Workflow

- **Formatting:** Enforced via Laravel Pint. Run `./vendor/bin/pint --format agent` before committing.
- **Frontend Check:** If UI styling looks broken, ensure `npm run build` or `npm run dev` is active, as Vite handles Tailwind v4 JIT compilation.
- **Branching:** Feature branches (`feature/new-api`) merged into `main` via Pull Requests.

## 16. Testing

We enforce 100% pass rates across rigorous financial logic tests.

```bash
# Run the entire PHPUnit suite
php artisan test

# Verify multi-signature thresholds
php artisan test tests/Feature/ClaimApprovalTest.php

# Verify immutable double-entry ledger logic
php artisan test tests/Feature/LedgerTest.php
```

## 17. Deployment

For modern cloud deployments (e.g., Laravel Cloud, Laravel Forge):

1. **Daemons:** Configure Supervisor to keep `horizon` and `reverb` alive.
2. **Cron:** Add `* * * * * cd /var/www/idir && php artisan schedule:run` to your crontab for hourly webhook cleanup and daily arrears checks.
3. **Octane (Optional):** Highly recommended to serve via Laravel Octane (FrankenPHP) for extreme concurrency.

## 18. CI/CD

GitHub Actions (`.github/workflows/ci.yml`) are configured to:
- Checkout code & Setup PHP 8.4.
- Cache Composer & NPM.
- Execute `php artisan test` against an in-memory SQLite DB.
- Prevent merges if coverage drops or tests fail.

## 19. Security

- **Webhooks:** `hash_equals` validation for Chapa HMAC signatures.
- **Authorization:** Eloquent Policies block Cross-Tenant data bleeding. Platform Owners are explicitly barred from viewing PII (Personally Identifiable Information) of standard members.
- **Audit Trails:** Deletions of financial data are impossible. Corrections must be explicitly recorded with a `denial_reason` or `audit_note`.

## 20. Contributing

We welcome civic technologists! 
1. Fork the project.
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`).
3. Ensure `php artisan test` passes.
4. Open a Pull Request.

## 21. Roadmap

- [x] Chapa / Telebirr Integration
- [x] Fayda OIDC
- [x] Multi-signature Disbursal
- [ ] Offline PWA support via Service Workers
- [ ] USSD Gateway for non-smartphone users
- [ ] Oromo & Tigrinya I18n translations

## 22. FAQ

**Q: Can a Platform Owner alter my Idir's ledger?**
A: No. Architectural boundaries in `MemberPolicy` isolate Tenant data entirely from Super Admins.

**Q: How are erroneous ledger entries fixed?**
A: Through offset corrections. A Treasurer logs an inverse payment flagged as `is_correction = true`, recalculating the balance dynamically while preserving the audit trail.

## 23. License

Distributed under the MIT License. See `LICENSE` for more information.

## 24. Acknowledgements

- Built for the communities of Ethiopia to digitize traditional **Idir (እድር)** practices.
- Powered by [Laravel](https://laravel.com), [Filament](https://filamentphp.com), [Chapa](https://chapa.co), and the [Fayda National ID Program](https://fayda.et).
