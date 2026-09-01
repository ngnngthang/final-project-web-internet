<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO users (school_id, username, email, password, role, is_active, created_at, updated_at)
             VALUES (:school_id, :username, :email, :password, :role, :is_active, NOW(), NOW())'
        );
        $stmt->execute([
            'school_id' => $data['school_id'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? true,
        ]);
        return (int) $pdo->lastInsertId();
    }
}
