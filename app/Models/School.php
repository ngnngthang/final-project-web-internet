<?php

namespace App\Models;

use App\Core\Database;

class School
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM schools WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM schools ORDER BY name')->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO schools (name, email, phone, address, tier, subscription_status, created_at, updated_at)
             VALUES (:name, :email, :phone, :address, :tier, :subscription_status, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'tier' => $data['tier'] ?? 'Starter',
            'subscription_status' => $data['subscription_status'] ?? 'trial',
        ]);
        return (int) $pdo->lastInsertId();
    }
}
