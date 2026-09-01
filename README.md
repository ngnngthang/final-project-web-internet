# ClassHub (Plain PHP)

Grade management SaaS for Vietnamese THPT secondary schools — plain PHP + PDO + MySQL, no framework, no Composer dependency required. Runs on XAMPP.

This replaces the earlier Laravel-based version. Same MVC design, same RBAC/Policy model, same Service-layer business logic — just implemented by hand instead of via a framework. See `docs/classhub_php_architecture.md` for the original architecture doc (mentally substitute "Eloquent Model" → "static PDO Model class", "Laravel Policy/Gate" → "Policy class", "Migration" → "schema.sql", "Sanctum" → "PHP session `Auth` helper").

## Quick Start

```bash
php run.php                # creates .env, creates DB, loads schema.sql, serves at :8000
php run.php --setup-only   # setup only, don't start the server
php run.php --fresh        # drop all tables, reload schema.sql, then serve
```

Requires MySQL running (e.g. XAMPP) reachable via `.env` (defaults: `127.0.0.1:3306`, user `root`, no password, db `classhub`). Requires PHP 8.1+ with the `pdo_mysql` extension enabled — no Composer, no `vendor/` folder.

## Project Structure

```
classhub-php/
├── public/
│   ├── index.php        # front controller — all requests route through here
│   └── .htaccess        # mod_rewrite: routes everything to index.php
├── app/
│   ├── autoload.php      # zero-dependency PSR-4-ish autoloader
│   ├── Core/
│   │   ├── Router.php        # {param} route matching
│   │   ├── Request.php       # reads JSON body + $_GET
│   │   ├── Response.php      # JSON response helper
│   │   ├── Database.php      # PDO singleton
│   │   ├── Auth.php          # session-based auth (replaces Sanctum)
│   │   └── helpers.php       # app_audit_log() global helper
│   ├── Controllers/      # thin — one per module, calls Services/Models
│   ├── Models/           # static PDO data-access classes, 1 per table
│   ├── Policies/         # RBAC — plain static methods, checked in Controllers
│   ├── Services/         # GradeCalculationService, EnrollmentValidationService, etc.
│   └── Exceptions/       # BusinessRuleException (422), ForbiddenException (403)
├── database/
│   └── schema.sql        # all 12 tables, raw DDL with CHECK constraints
├── routes/
│   └── api.php           # route table, included by public/index.php
├── config/
│   └── config.php        # reads .env
├── .env.example
├── run.php                # setup + serve script
└── run.sh                 # Bash alternative (if you have a Unix shell)
```

## XAMPP Setup (manual, if not using run.php)

### 1. Start XAMPP
Apache + MySQL, via XAMPP Control Panel.

### 2. Configure environment
```bash
cp .env.example .env
```
Edit `.env` if your MySQL credentials differ from XAMPP defaults.

### 3. Create database + load schema
```bash
mysql -u root -e "CREATE DATABASE classhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root classhub < database/schema.sql
```
**MySQL 8.0.16+ required** for the `CHECK` constraints (score range, capacity range, weight range) to actually be enforced — earlier versions parse but silently ignore them.

### 4. Serve
Either:
```bash
php -S 127.0.0.1:8000 -t public
```
...or point an XAMPP vhost/alias at `classhub-php/public` and visit `http://localhost/classhub-php/public`.

## Design Notes (differences from the Laravel version)

| Concern | Laravel version | This version |
|---|---|---|
| Routing | `routes/api.php` + Route facade | `app/Core/Router.php`, `{param}` regex matching |
| ORM | Eloquent Models | Static PDO Model classes (`Student::find()`, `Student::create()`) |
| Migrations | `database/migrations/*.php` | Single `database/schema.sql`, run once |
| RBAC | Laravel Policies + Gate | Plain `Policy` classes, checked manually in each Controller action |
| Auth | Sanctum (session + token) | Native PHP `session_start()` + a small `Auth` helper class |
| Grade recalculation | Model Observer (auto-fires on save) | Controller explicitly calls `GradeCalculationService::recalculate()` after every score write — there's no observer mechanism without a framework, so this call is not optional; don't forget it in any new code path that writes to `scores` |
| Dependency management | Composer | None — a small custom autoloader maps `App\Foo\Bar` → `app/Foo/Bar.php` |
| Sessions/Queue | Laravel `database` driver | PHP native sessions; no queue system (add one only if a real async need appears) |

## What's Implemented vs. Stubbed

| Layer | Status |
|---|---|
| `database/schema.sql` (12 tables) | ✅ Complete |
| Models (12, static PDO classes) | ✅ Complete |
| Policies (Lop, Enrollment, Grade, Student) | ✅ Complete |
| GradeCalculationService | ✅ Complete |
| EnrollmentValidationService | ✅ Complete |
| AuditLogService / `app_audit_log()` | ✅ Complete |
| BulkImportService | ⏳ Minimal CSV parser — needs full validation per spec |
| GradeController (core) | ✅ Complete — store/publish/unpublish/index/myGrades |
| EnrollmentController | ✅ Complete |
| Khoi/Lop/Student/Teacher/Schedule Controllers | ⏳ Basic CRUD only, no edge-case validation yet |
| Frontend | ⏳ Not started — API-only for now |
| Tests | ⏳ Not started |

## Next Steps

1. Add input validation (currently trusts request data beyond basic type casting)
2. Finish `BulkImportService` per the Module 2 spec (duplicate checks, credential generation/export)
3. Write tests for each Policy — since there's no framework enforcing RBAC, this is the only real defense (same caution as the Laravel version's ADR-6, just doubly true here with no framework backstop at all)
4. Decide on a frontend and add CORS/auth accordingly

## Technologies

- **Backend**: PHP 8.1+, no framework
- **Database**: MySQL 8.0.16+ (via XAMPP)
- **Auth**: Native PHP sessions
- **Server**: Apache (XAMPP) or PHP's built-in server
