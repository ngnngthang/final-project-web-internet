<?php

namespace App\Policies;

use App\Models\User;

class StudentPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Staff']);
    }

    public function bulkImport(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Staff']);
    }
}
