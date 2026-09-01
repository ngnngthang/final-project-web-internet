<?php

namespace App\Core;

class Request
{
    private array $body;

    public function __construct()
    {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        $this->body = is_array($decoded) ? $decoded : $_POST;
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        // Strip a leading /api if the app is served from a subfolder in XAMPP htdocs
        $path = preg_replace('#^/api#', '', $path);
        return rtrim($path, '/') ?: '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $this->body);
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}
