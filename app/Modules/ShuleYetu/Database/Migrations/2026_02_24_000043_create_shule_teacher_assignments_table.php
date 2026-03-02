<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_teacher_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('shule_academic_years')->cascadeOnDelete();
            $table->foreignUuid('term_id')->constrained('shule_terms')->cascadeOnDelete();
            $table->foreignUuid('staff_id')->constrained('shule_staff')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('shule_classes')->cascadeOnDelete();
            $table->foreignUuid('stream_id')->nullable()->constrained('shule_streams')->nullOnDelete();
            $table->foreignUuid('subject_id')->nullable()->constrained('shule_subjects')->nullOnDelete();
            $table->boolean('is_class_teacher')->default(false);
            $table->timestamps();

            $table->unique(
                ['school_id', 'term_id', 'staff_id', 'class_id', 'stream_id', 'subject_id'],
                'shule_teacher_assignments_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_teacher_assignments');
    }
};
