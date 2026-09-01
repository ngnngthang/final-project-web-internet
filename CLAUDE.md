# CLAUDE.md

Guidance for Claude Code when working in this repository.

## Project

ClassHub — grade management SaaS for Vietnamese THPT secondary schools.
Stack: **plain PHP 8.1+, PDO, MySQL 8.0.16+. No framework, no Composer.**
(Earlier version used Laravel — this repo intentionally reverted to
framework-free PHP. Don't reintroduce Composer/Laravel without being asked.)

## Setup & Run

```bash
php run.php                # setup (env, DB, schema) + serve at :8000
php run.php --setup-only   # setup only
php run.php --fresh        # drop all tables, reload schema.sql, then serve
```

Requires MySQL running locally (e.g. XAMPP) reachable via `.env` (defaults:
`127.0.0.1:3306`, user `root`, no password, db `classhub`).

## Common Commands

```bash
php -S 127.0.0.1:8000 -t public          # serve directly, no setup steps
mysql -u root classhub < database/schema.sql   # (re)load schema manually
```

There is no test runner, package manager, or build step in this project —
it is deliberately dependency-free.

## Architecture

Full design notes: `README.md` "Design Notes" table, and
`docs/classhub_php_architecture.md` (the original Laravel-era doc — still
useful for the *business logic* design, just mentally substitute Eloquent →
static PDO Models, Policies/Gate → plain Policy classes, migrations →
`schema.sql`, Sanctum → `App\Core\Auth`).

Key points an agent should know before editing:

- **MVC + Service layer**: Controllers (`app/Controllers/*`) stay thin —
  auth/policy checks, input read, then delegate to `app/Services/*` for
  business logic and `app/Models/*` for data access. Don't put SQL directly
  in a Controller.
- **RBAC via Policies only** (`app/Policies/*`), checked manually at the top
  of each Controller action with `throw new ForbiddenException(...)` on
  failure. There is no framework enforcing this — every new
  create/update/delete/view action MUST start with an explicit Policy check.
- **No Observer/trigger for grade recalculation.** Any code path that writes
  to the `scores` table MUST explicitly call
  `(new GradeCalculationService())->recalculate($studentId, $lopId)`
  afterward (see `GradeController::store()` for the pattern). This is easy
  to forget without a framework auto-firing it — check for this whenever
  adding a new score-writing endpoint.
- **No dependency injection container.** Controllers `new` up Services
  directly. Keep it that way — don't introduce a container for a project
  this size.
- **Routing**: `routes/api.php` registers `{param}` routes on the `Router`
  instance built in `public/index.php`. Add new routes there, not inline.
- **IDs are `BIGINT UNSIGNED AUTO_INCREMENT`** — follow this convention in
  any new table added to `database/schema.sql`.
- **`app_audit_log()`** (global helper, `app/Core/helpers.php`) should be
  called after any write that needs an audit trail (score entry, grade
  publish, enrollment) — see existing Controllers for the call signature.

## Current State

See `README.md`'s "What's Implemented vs. Stubbed" table. In short: schema,
Models, core Policies, and the Grade/Enrollment Services and Controllers are
implemented. Khoi/Lop/Student/Teacher/Schedule Controllers are basic CRUD
without full edge-case validation. No tests, no frontend yet.

## Conventions

- One Model class per table, all static methods, PDO prepared statements
  only — never interpolate user input into SQL.
- New business rules that can fail validly (not a bug, but "not allowed")
  throw `App\Exceptions\BusinessRuleException` with a short error code
  (Router catches it and returns the standard 422 JSON error shape).
  Permission failures throw `App\Exceptions\ForbiddenException` (→ 403).
- Match the existing Controller pattern: `Auth::require()` first, then a
  Policy check, then call a Service or Model, then return
  `Response::json([...], $status)`.
