<?php

namespace App\Policies;

use App\Models\FinalGrade;
use App\Models\Lop;
use App\Models\Score;
use App\Models\User;

class GradePolicy
{
    public function enter(User $user, Lop $lop): bool
    {
        return $user->role === 'Admin'
            || ($user->role === 'Teacher' && $lop->teacher_id === $user->teacher?->id);
    }

    public function publish(User $user, Lop $lop): bool
    {
        return $this->enter($user, $lop); // same rule per permission matrix
    }

    public function correct(User $user, Score $score): bool
    {
        return $user->role === 'Admin'
            || ($user->role === 'Teacher' && $score->lop->teacher_id === $user->teacher?->id);
    }

    public function viewAll(User $user): bool
    {
        return $user->role === 'Admin';
    }

    public function viewOwn(User $user, FinalGrade $grade): bool
    {
        return $user->role === 'Student' && $grade->student_id === $user->student?->id;
    }
}
