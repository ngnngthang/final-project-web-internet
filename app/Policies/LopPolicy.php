<?php

namespace App\Policies;

use App\Models\Lop;
use App\Models\User;

class LopPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'Admin';
    }

    public function assignTeacher(User $user): bool
    {
        return $user->role === 'Admin';
    }

    public function view(User $user, Lop $lop): bool
    {
        return match ($user->role) {
            'Admin', 'Staff' => true,
            'Teacher' => $lop->teacher_id === $user->teacher?->id,
            'Student' => $user->student?->enrollments()->where('lop_id', $lop->id)->exists(),
            default => false,
        };
    }
}
