<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name', 100);
            $table->string('student_id', 20);
            $table->date('date_of_birth');
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Graduated', 'Transferred'])->default('Active');
            $table->timestamps();

            $table->unique(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
