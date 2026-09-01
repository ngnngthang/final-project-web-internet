<?php

namespace App\Policies;

use App\Core\Auth;
use App\Models\Teacher;

class LopPolicy
{
    public static function create(): bool
    {
        return Auth::role() === 'Admin';
    }

    public static function assignTeacher(): bool
    {
        return Auth::role() === 'Admin';
    }

    public static function view(array $lop): bool
    {
        return match (Auth::role()) {
            'Admin', 'Staff' => true,
            'Teacher' => self::ownsLop($lop),
            'Student' => true, // further narrowed by enrollment check in controller
            default => false,
        };
    }

    public static function ownsLop(array $lop): bool
    {
        $teacher = Teacher::findByUserId(Auth::id());
        return $teacher && (int) $lop['teacher_id'] === (int) $teacher['id'];
    }
}
