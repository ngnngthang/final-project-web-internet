<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khoi_id')->constrained('khoi')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('name', 20);
            $table->unsignedInteger('max_capacity')->default(50);
            $table->unsignedInteger('current_enrollment')->default(0);
            $table->enum('status', ['Planning', 'Active', 'Archived'])->default('Planning');
            $table->timestamps();

            $table->unique(['khoi_id', 'name']);
        });

        // MySQL 8.0.16+ required for native CHECK constraints
        DB::statement('ALTER TABLE lop ADD CONSTRAINT chk_capacity CHECK (max_capacity BETWEEN 10 AND 200)');
    }

    public function down(): void
    {
        Schema::dropIfExists('lop');
    }
};
