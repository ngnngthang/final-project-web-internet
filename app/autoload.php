<?php

/**
 * Zero-dependency autoloader. Maps namespace App\Foo\Bar -> app/Foo/Bar.php.
 * Avoids requiring Composer just to run this project (matches the "plain
 * PHP, no framework" decision) — swap for composer's vendor/autoload.php
 * if you later add dependencies.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

require __DIR__ . '/../app/Core/helpers.php';
