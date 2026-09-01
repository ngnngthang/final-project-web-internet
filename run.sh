#!/usr/bin/env bash
#
# ClassHub — setup & run script (Bash alternative to run.php)
#
# Creates .env, creates the database if missing, loads database/schema.sql,
# and starts PHP's built-in server pointed at public/.
#
# Usage:
#   ./run.sh              # setup + serve
#   ./run.sh --setup-only # setup only, don't start the server
#   ./run.sh --fresh      # drop all tables, reload schema.sql, then serve

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

SETUP_ONLY=false
FRESH=false
for arg in "$@"; do
  case "$arg" in
    --setup-only) SETUP_ONLY=true ;;
    --fresh) FRESH=true ;;
    -h|--help)
      grep '^#' "$0" | sed 's/^#//'
      exit 0
      ;;
    *)
      echo "Unknown option: $arg" >&2
      exit 1
      ;;
  esac
done

log()  { printf '\n\033[1;34m==>\033[0m %s\n' "$1"; }
ok()   { printf '\033[1;32m✓\033[0m %s\n' "$1"; }
fail() { printf '\033[1;31m✗ %s\033[0m\n' "$1" >&2; exit 1; }

# ---------------------------------------------------------------------------
# 1. Pre-flight checks
# ---------------------------------------------------------------------------
log "Checking prerequisites"

command -v php >/dev/null 2>&1 || fail "PHP not found. Install PHP 8.1+ (or start XAMPP and add its php.exe to PATH)."

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
ok "PHP $PHP_VERSION found"

if ! php -m | grep -qi pdo_mysql; then
  fail "pdo_mysql PHP extension not enabled. Enable it in php.ini (extension=pdo_mysql) and restart Apache/PHP."
fi
ok "pdo_mysql extension enabled"

# ---------------------------------------------------------------------------
# 2. Environment file
# ---------------------------------------------------------------------------
log "Configuring environment"

if [ ! -f ".env" ]; then
  cp .env.example .env
  ok "Created .env from .env.example (XAMPP MySQL defaults: 127.0.0.1:3306, user=root, no password, db=classhub)"
else
  ok ".env already exists — leaving it untouched"
fi

DB_DATABASE=$(grep -E '^DB_DATABASE=' .env | cut -d '=' -f2- | tr -d '\r')
DB_HOST=$(grep -E '^DB_HOST=' .env | cut -d '=' -f2- | tr -d '\r')
DB_PORT=$(grep -E '^DB_PORT=' .env | cut -d '=' -f2- | tr -d '\r')
DB_USERNAME=$(grep -E '^DB_USERNAME=' .env | cut -d '=' -f2- | tr -d '\r')
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' .env | cut -d '=' -f2- | tr -d '\r')

# ---------------------------------------------------------------------------
# 3. Database — create it if missing, load schema.sql
# ---------------------------------------------------------------------------
log "Checking database"

if ! command -v mysql >/dev/null 2>&1; then
  fail "mysql CLI not found on PATH. Either add it (XAMPP: xampp/mysql/bin) or use 'php run.php' instead, which needs no CLI."
fi

MYSQL_PW_ARG=()
[ -n "$DB_PASSWORD" ] && MYSQL_PW_ARG=(-p"$DB_PASSWORD")

if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "${MYSQL_PW_ARG[@]}" \
    -e "USE \`$DB_DATABASE\`;" >/dev/null 2>&1; then
  ok "Database '$DB_DATABASE' already exists"
else
  log "Creating database '$DB_DATABASE'"
  mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "${MYSQL_PW_ARG[@]}" \
    -e "CREATE DATABASE \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
    || fail "Could not create database. Is MySQL running (start it in XAMPP Control Panel)?"
  ok "Database created"
fi

if [ "$FRESH" = true ]; then
  log "--fresh: dropping existing tables"
  TABLES=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "${MYSQL_PW_ARG[@]}" -N -e \
    "SELECT table_name FROM information_schema.tables WHERE table_schema='$DB_DATABASE';")
  if [ -n "$TABLES" ]; then
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "${MYSQL_PW_ARG[@]}" "$DB_DATABASE" \
      -e "SET FOREIGN_KEY_CHECKS=0; $(echo "$TABLES" | sed 's/^/DROP TABLE IF EXISTS `/;s/$/`;/' | tr '\n' ' ') SET FOREIGN_KEY_CHECKS=1;"
  fi
  ok "Existing tables dropped"
fi

TABLE_COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "${MYSQL_PW_ARG[@]}" -N -e \
  "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_DATABASE';")

if [ "$TABLE_COUNT" -eq 0 ]; then
  log "Loading schema"
  mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "${MYSQL_PW_ARG[@]}" "$DB_DATABASE" < database/schema.sql
  ok "Schema loaded (12 ClassHub tables created)"
else
  ok "Tables already exist ($TABLE_COUNT found) — skipping schema load. Use --fresh to reload."
fi

if [ "$SETUP_ONLY" = true ]; then
  echo
  ok "Setup complete. Run without --setup-only to start the server."
  exit 0
fi

# ---------------------------------------------------------------------------
# 4. Serve
# ---------------------------------------------------------------------------
log "Starting ClassHub at http://127.0.0.1:8000"
echo "  (Ctrl+C to stop. Or serve via XAMPP by pointing a vhost/alias at ./public instead.)"
echo
php -S 127.0.0.1:8000 -t public
