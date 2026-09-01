<?php

namespace App\Providers;

use App\Models\Enrollment;
use App\Models\FinalGrade;
use App\Models\Lop;
use App\Models\Score;
use App\Models\Student;
use App\Observers\EnrollmentObserver;
use App\Observers\ScoreObserver;
use App\Policies\EnrollmentPolicy;
use App\Policies\GradePolicy;
use App\Policies\LopPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // RBAC — see classhub_php_architecture.md Section 6
        Gate::policy(Lop::class, LopPolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);
        Gate::policy(Score::class, GradePolicy::class);
        Gate::policy(FinalGrade::class, GradePolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);

        // Grade recalculation + audit trail — see architecture doc Section 7
        Score::observe(ScoreObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
    }
}
