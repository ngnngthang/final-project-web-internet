<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Services\AuditLogService;

class EnrollmentObserver
{
    public function created(Enrollment $enrollment): void
    {
        app(AuditLogService::class)->log('enrolled_student', $enrollment);
    }
}
