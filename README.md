# ClassHub

Grade management SaaS for Vietnamese THPT secondary schools — built with Laravel (MVC) and MySQL, running on XAMPP for local development.

See `classhub_php_architecture.md` (in the project docs) for the full architecture, ADRs, and Policy/Service design this structure implements.

## Project Structure

```
classhub/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/          # JSON API controllers (stubs — see TODOs)
│   │   └── Web/          # Blade-view controllers (only if server-rendered frontend is chosen)
│   ├── Models/           # Eloquent models — 1 per table, relationships wired
│   ├── Policies/         # RBAC — maps directly to the permission matrix
│   ├── Services/         # GradeCalculationService, EnrollmentValidationService, etc.
│   ├── Observers/        # ScoreObserver (grade recalc), EnrollmentObserver (audit log)
│   └── Exceptions/       # BusinessRuleException (enrollment/capacity errors)
├── database/
│   └── migrations/       # All 12 ClassHub tables, in dependency order
├── routes/
│   ├── api.php           # Primary route file — build here regardless of frontend choice
│   └── web.php           # Only used if server-rendered frontend is chosen
└── config/database.php   # MySQL connection (via .env)
```

**Status**: Structural scaffold only. Controllers are stubs; Models, Policies, Services, and Observers for the core Grade Management flow are implemented per the architecture doc. Business logic for the remaining modules still needs to be filled in.

---

## XAMPP Setup

### 1. Start XAMPP
Open XAMPP Control Panel → start **Apache** and **MySQL**.

### 2. Install PHP dependencies

This sandbox couldn't reach Packagist to run `composer install` — do this on your machine:

```bash
cd classhub
composer install
```

This pulls in `laravel/framework`, `laravel/sanctum` (needed for the `auth:sanctum` middleware used in `routes/api.php`), and dev tooling.

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

`.env.example` is already set to XAMPP's MySQL defaults:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=classhub
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Create the database

Open phpMyAdmin (`http://localhost/phpmyadmin`) and create a database named `classhub` — or via terminal:
```bash
mysql -u root -e "CREATE DATABASE classhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**MySQL 8.0.16+ required** — earlier versions silently ignore the `CHECK` constraints in the migrations (score range, capacity range, weight range).

### 5. Run migrations

```bash
php artisan migrate
```

This creates all 12 ClassHub tables plus Laravel's `sessions`, `cache`, and `jobs` tables (used instead of Redis — see ADR-4 in the architecture doc).

### 6. Serve the app

Either use Laravel's built-in server:
```bash
php artisan serve
```
Visit `http://localhost:8000`

...or point an XAMPP vhost at `classhub/public` and visit `http://localhost/classhub/public`.

---

## What's Implemented vs. Stubbed

| Layer | Status |
|---|---|
| Migrations (12 tables) | ✅ Complete |
| Models + relationships | ✅ Complete |
| Policies (RBAC) | ✅ Complete for Lop, Enrollment, Grade, Student |
| GradeCalculationService | ✅ Complete (weighted-sum logic) |
| EnrollmentValidationService | ✅ Complete (capacity/duplicate checks, atomic transaction) |
| ScoreObserver / EnrollmentObserver | ✅ Complete (auto-recalc + audit log) |
| BulkImportService | ⏳ Stub — see Module 2 spec |
| AuditLogService | ✅ Complete |
| Controllers (Api/Web) | ⏳ Stubs — wire up Services above |
| Views (Blade) | ⏳ Not started — pending frontend decision |
| Tests | ⏳ Not started |

## Next Steps

1. Decide the frontend approach (server-rendered Blade vs. separate SPA) — see the open item in `classhub_php_architecture.md`
2. Implement `Api\GradeController` (highest priority — core module)
3. Add PHPUnit tests per Policy class (per ADR-6, this is the sole RBAC enforcement layer — needs full coverage)
4. Implement `BulkImportService`

## Technologies

- **Backend**: PHP 8.2+, Laravel 11
- **Database**: MySQL 8.0.16+ (via XAMPP)
- **Auth**: Laravel Sanctum (session + token, dual-mode)
- **Sessions/Queue**: `database` driver (no Redis — single-server deployment)
