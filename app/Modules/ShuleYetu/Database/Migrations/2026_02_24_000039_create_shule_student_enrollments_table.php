<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_student_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('shule_students')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('shule_academic_years')->cascadeOnDelete();
            $table->foreignUuid('class_id')->nullable()->constrained('shule_classes')->nullOnDelete();
            $table->foreignUuid('stream_id')->nullable()->constrained('shule_streams')->nullOnDelete();
            $table->date('enrollment_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->enum('status', ['enrolled', 'promoted', 'repeated', 'left'])->default('enrolled');
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'academic_year_id'], 'shule_enroll_school_student_year_uq');
            $table->index('student_id');
            $table->index('academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_student_enrollments');
    }
};

