<?php

namespace App\Policies;

use App\Core\Auth;
use App\Models\Teacher;

class GradePolicy
{
    public static function enter(array $lop): bool
    {
        return Auth::role() === 'Admin' || (Auth::role() === 'Teacher' && LopPolicy::ownsLop($lop));
    }

    public static function publish(array $lop): bool
    {
        return self::enter($lop); // same rule per permission matrix
    }

    public static function correct(array $lop): bool
    {
        return self::enter($lop);
    }

    public static function viewAll(): bool
    {
        return Auth::role() === 'Admin';
    }

    public static function viewOwn(array $finalGrade): bool
    {
        if (Auth::role() !== 'Student') {
            return false;
        }
        $student = \App\Models\Student::findByUserId(Auth::id());
        return $student && (int) $finalGrade['student_id'] === (int) $student['id'];
    }
}
