<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lop_id')->constrained('lop')->cascadeOnDelete();
            $table->string('academic_year', 10);
            $table->date('enrollment_date')->useCurrent();
            $table->enum('status', ['Enrolled', 'Inactive', 'Dropped', 'Graduated'])->default('Enrolled');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['student_id', 'lop_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
