<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_exam_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('exam_id')->constrained('shule_exams')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('shule_subjects')->cascadeOnDelete();
            $table->integer('max_marks');
            $table->integer('pass_mark')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'exam_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_exam_subjects');
    }
};
