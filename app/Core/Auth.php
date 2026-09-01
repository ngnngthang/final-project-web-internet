<?php

namespace App\Core;

use App\Models\User;

/**
 * Session-based auth. Replaces Laravel Sanctum — no framework, no tokens
 * needed for a single server-rendered/API-hybrid app talking to its own
 * frontend on the same origin.
 */
class Auth
{
    public static function attempt(string $username, string $password): ?array
    {
        $user = User::findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['school_id'] = $user['school_id'];
            return $user;
        }
        return null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return User::find($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /** Throws ForbiddenException if not logged in. Call at the top of protected controller actions. */
    public static function require(): void
    {
        if (!self::check()) {
            throw new \App\Exceptions\ForbiddenException('Authentication required');
        }
    }
}
