<?php

namespace App\Policies;

use App\Core\Auth;

class StudentPolicy
{
    public static function create(): bool
    {
        return in_array(Auth::role(), ['Admin', 'Staff'], true);
    }

    public static function bulkImport(): bool
    {
        return in_array(Auth::role(), ['Admin', 'Staff'], true);
    }
}
