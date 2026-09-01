<?php

namespace App\Models;

use App\Core\Database;

class Schedule
{
    public static function forLop(int $lopId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM schedules WHERE lop_id = ?');
        $stmt->execute([$lopId]);
        $row = $stmt->fetch();
        if ($row) {
            $row['days_of_week'] = json_decode($row['days_of_week'], true);
        }
        return $row ?: null;
    }

    public static function upsert(array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO schedules (lop_id, school_id, days_of_week, start_time, end_time, location, created_at, updated_at)
             VALUES (:lop_id, :school_id, :days_of_week, :start_time, :end_time, :location, NOW(), NOW())
             ON DUPLICATE KEY UPDATE days_of_week = VALUES(days_of_week), start_time = VALUES(start_time),
                end_time = VALUES(end_time), location = VALUES(location), updated_at = NOW()'
        );
        $stmt->execute([
            'lop_id' => $data['lop_id'],
            'school_id' => $data['school_id'],
            'days_of_week' => json_encode($data['days_of_week']),
            'start_time' => $data['start_time'] ?? '07:00:00',
            'end_time' => $data['end_time'] ?? '12:30:00',
            'location' => $data['location'] ?? null,
        ]);
    }
}
