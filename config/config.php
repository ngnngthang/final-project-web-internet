<?php

/**
 * Minimal .env loader — no Composer package needed.
 * Reads KEY=VALUE lines from .env into $_ENV / getenv(), skipping comments/blank lines.
 */
function load_env(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

load_env(__DIR__ . '/../.env');

return [
    'app' => [
        'name' => env('APP_NAME', 'ClassHub'),
        'debug' => env('APP_DEBUG', 'true') === 'true',
        'url' => env('APP_URL', 'http://localhost:8000'),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'classhub'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'lifetime' => (int) env('SESSION_LIFETIME', 120), // minutes
    ],
];
