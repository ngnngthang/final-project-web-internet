<?php

namespace App\Observers;

use App\Models\Score;
use App\Services\AuditLogService;
use App\Services\GradeCalculationService;

class ScoreObserver
{
    public function saved(Score $score): void
    {
        app(GradeCalculationService::class)->recalculate($score->student_id, $score->lop_id);

        app(AuditLogService::class)->log(
            action: $score->wasRecentlyCreated ? 'entered_score' : 'corrected_score',
            model: $score,
            changes: $score->getChanges()
        );
    }
}
