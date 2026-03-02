<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_exam_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('exam_subject_id')->constrained('shule_exam_subjects')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('shule_students')->cascadeOnDelete();
            $table->decimal('marks_obtained', 6, 2);
            $table->decimal('percentage', 6, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('remarks')->nullable();
            $table->foreignId('entered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'exam_subject_id', 'student_id'], 'shule_exam_scores_unique');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_exam_scores');
    }
};
