<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_id')->constrained('lop')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained();
            $table->json('days_of_week'); // e.g. ["Mon","Wed","Fri"]
            $table->time('start_time')->default('07:00:00');
            $table->time('end_time')->default('12:30:00');
            $table->string('location', 100)->nullable();
            $table->timestamps();

            $table->unique('lop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
