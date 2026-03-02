<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_fee_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('shule_academic_years')->cascadeOnDelete();
            $table->foreignUuid('term_id')->constrained('shule_terms')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('shule_classes')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'academic_year_id', 'term_id', 'class_id'], 'shule_fee_structures_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_fee_structures');
    }
};
