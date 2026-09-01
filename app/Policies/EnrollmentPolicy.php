<?php

namespace App\Policies;

use App\Models\User;

class EnrollmentPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Staff']);
    }

    public function delete(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Staff']);
    }
}
