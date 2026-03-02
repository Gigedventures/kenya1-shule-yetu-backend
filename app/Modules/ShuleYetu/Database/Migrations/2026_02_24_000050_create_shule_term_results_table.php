<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_term_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('shule_academic_years')->cascadeOnDelete();
            $table->foreignUuid('term_id')->constrained('shule_terms')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('shule_students')->cascadeOnDelete();
            $table->decimal('total_marks', 10, 2);
            $table->decimal('total_percentage', 6, 2);
            $table->decimal('average', 6, 2);
            $table->string('overall_grade')->nullable();
            $table->integer('rank')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'term_id', 'student_id'], 'shule_term_results_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_term_results');
    }
};
