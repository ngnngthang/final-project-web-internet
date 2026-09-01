<?php

namespace App\Policies;

use App\Core\Auth;

class EnrollmentPolicy
{
    public static function create(): bool
    {
        return in_array(Auth::role(), ['Admin', 'Staff'], true);
    }

    public static function delete(): bool
    {
        return in_array(Auth::role(), ['Admin', 'Staff'], true);
    }
}
