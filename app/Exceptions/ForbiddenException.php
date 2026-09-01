<?php

namespace App\Exceptions;

use Exception;

/** Thrown by Policies / Auth::require() when a role is not permitted. Router returns 403. */
class ForbiddenException extends Exception
{
}
