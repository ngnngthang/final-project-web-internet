<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lop_id')->constrained('lop')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained();
            $table->string('academic_year', 10);
            $table->decimal('final_score', 4, 2)->nullable();
            $table->boolean('is_complete')->default(false);
            $table->enum('pass_status', ['Pass', 'Fail', 'Incomplete'])->default('Incomplete');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['student_id', 'lop_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_grades');
    }
};
