<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lop_id')->constrained('lop')->cascadeOnDelete();
            $table->foreignId('assessment_type_id')->constrained();
            $table->foreignId('teacher_id')->constrained('teachers');
            $table->foreignId('school_id')->constrained();
            $table->decimal('numeric_score', 4, 2)->nullable();
            $table->string('notes', 255)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['student_id', 'lop_id', 'assessment_type_id']);
        });

        DB::statement('ALTER TABLE scores ADD CONSTRAINT chk_score CHECK (numeric_score IS NULL OR numeric_score BETWEEN 0 AND 10)');
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
