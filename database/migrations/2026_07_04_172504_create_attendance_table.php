<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('shule_academic_years')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('shule_terms')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('shule_students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('shule_classes')->cascadeOnDelete();
            $table->foreignId('stream_id')->nullable()->constrained('shule_streams')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('shule_subjects')->cascadeOnDelete();
            $table->foreignId('marked_by')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'attendance_date', 'subject_id'], 'unique_student_daily_attendance');
            $table->index(['school_id', 'academic_year_id', 'term_id']);
            $table->index(['class_id', 'attendance_date']);
            $table->index(['student_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};