<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->decimal('weight', 5, 2);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE assessment_types ADD CONSTRAINT chk_weight CHECK (weight BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_types');
    }
};
