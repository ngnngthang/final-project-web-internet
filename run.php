<?php
/**
 * ClassHub — setup & run script (plain PHP, no framework, no Composer)
 *
 * Creates .env, creates the database if missing, loads database/schema.sql,
 * and starts PHP's built-in server pointed at public/.
 *
 * Usage:
 *   php run.php                 setup + serve
 *   php run.php --setup-only    setup only, don't start the server
 *   php run.php --fresh         drop all tables first, then reload schema.sql
 */

$root = __DIR__;
chdir($root);

$setupOnly = in_array('--setup-only', $argv, true);
$fresh = in_array('--fresh', $argv, true);
if (in_array('-h', $argv, true) || in_array('--help', $argv, true)) {
    echo <<<HELP
    Usage:
      php run.php                 setup + serve
      php run.php --setup-only    setup only, don't start the server
      php run.php --fresh         drop all tables, reload schema.sql, then serve
    HELP;
    exit(0);
}

function log_step(string $msg): void
{
    echo "\n\033[1;34m==>\033[0m {$msg}\n";
}
function ok(string $msg): void
{
    echo "\033[1;32m✓\033[0m {$msg}\n";
}
function fail(string $msg): never
{
    fwrite(STDERR, "\033[1;31m✗ {$msg}\033[0m\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// 1. Pre-flight checks
// ---------------------------------------------------------------------------
log_step('Checking prerequisites');

if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    fail('PHP 8.1+ required, found ' . PHP_VERSION);
}
ok('PHP ' . PHP_VERSION . ' found');

if (!extension_loaded('pdo_mysql')) {
    fail('pdo_mysql PHP extension not enabled. Enable it in php.ini (extension=pdo_mysql) and restart Apache/PHP.');
}
ok('pdo_mysql extension enabled');

// ---------------------------------------------------------------------------
// 2. Environment file
// ---------------------------------------------------------------------------
log_step('Configuring environment');

$envPath = $root . '/.env';
$envExamplePath = $root . '/.env.example';

if (!file_exists($envPath)) {
    if (!file_exists($envExamplePath)) {
        fail('.env.example not found — cannot bootstrap .env');
    }
    copy($envExamplePath, $envPath);
    ok('Created .env from .env.example (XAMPP MySQL defaults: 127.0.0.1:3306, user=root, no password, db=classhub)');
} else {
    ok('.env already exists — leaving it untouched');
}

function envValue(string $content, string $key, string $default = ''): string
{
    if (preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $m)) {
        return trim($m[1], " \t\n\r\0\x0B\"'");
    }
    return $default;
}

$envContent = file_get_contents($envPath);
$dbDatabase = envValue($envContent, 'DB_DATABASE', 'classhub');
$dbHost = envValue($envContent, 'DB_HOST', '127.0.0.1');
$dbPort = envValue($envContent, 'DB_PORT', '3306');
$dbUsername = envValue($envContent, 'DB_USERNAME', 'root');
$dbPassword = envValue($envContent, 'DB_PASSWORD', '');

// ---------------------------------------------------------------------------
// 3. Database — create if missing (direct PDO, no mysql CLI needed)
// ---------------------------------------------------------------------------
log_step('Checking database');

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort}",
        $dbUsername,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query('SHOW DATABASES LIKE ' . $pdo->quote($dbDatabase));
    $exists = $stmt->fetch() !== false;

    if (!$exists) {
        log_step("Creating database '{$dbDatabase}'");
        $pdo->exec("CREATE DATABASE `{$dbDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        ok('Database created');
    } else {
        ok("Database '{$dbDatabase}' already exists");
    }
} catch (PDOException $e) {
    fail(
        "Could not connect to MySQL at {$dbHost}:{$dbPort} as '{$dbUsername}'. "
        . "Is MySQL running (start it in XAMPP Control Panel)? Underlying error: " . $e->getMessage()
    );
}

// ---------------------------------------------------------------------------
// 4. Load schema
// ---------------------------------------------------------------------------
log_step('Loading schema');

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbDatabase};charset=utf8mb4",
        $dbUsername,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($fresh) {
        log_step('--fresh: dropping existing tables');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        ok('Existing tables dropped');
    }

    $tableCount = (int) $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ' . $pdo->quote($dbDatabase))->fetchColumn();

    if ($tableCount === 0) {
        $sql = file_get_contents($root . '/database/schema.sql');
        if ($sql === false) {
            fail('database/schema.sql not found');
        }
        // Split on semicolons at end of statements (schema.sql has no semicolons inside strings/JSON defaults)
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }
            $pdo->exec($statement);
        }
        ok('Schema loaded (12 ClassHub tables created)');
    } else {
        ok("Tables already exist ({$tableCount} found) — skipping schema load. Use --fresh to reload.");
    }
} catch (PDOException $e) {
    fail('Failed to load schema: ' . $e->getMessage());
}

if ($setupOnly) {
    echo "\n";
    ok('Setup complete. Run without --setup-only to start the server.');
    exit(0);
}

// ---------------------------------------------------------------------------
// 5. Serve
// ---------------------------------------------------------------------------
log_step('Starting ClassHub at http://127.0.0.1:8000');
echo "  (Ctrl+C to stop. Or serve via XAMPP by pointing a vhost/alias at ./public instead.)\n\n";

$publicDir = $root . DIRECTORY_SEPARATOR . 'public';
passthru('php -S 127.0.0.1:8000 -t ' . escapeshellarg($publicDir));
