<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('shule_academic_years')->cascadeOnDelete();
            $table->foreignUuid('term_id')->constrained('shule_terms')->cascadeOnDelete();
            $table->foreignUuid('exam_type_id')->constrained('shule_exam_types')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('shule_classes')->cascadeOnDelete();
            $table->foreignUuid('stream_id')->nullable()->constrained('shule_streams')->nullOnDelete();
            $table->string('title');
            $table->integer('total_marks')->default(100);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();

            $table->index('term_id');
            $table->index('class_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_exams');
    }
};
