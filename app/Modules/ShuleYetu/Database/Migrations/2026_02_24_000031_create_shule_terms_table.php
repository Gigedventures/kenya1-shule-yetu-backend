<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('school_id');
            $table->uuid('academic_year_id');

            // 🔥 LIMIT STRING LENGTH EXPLICITLY
            $table->string('name', 100);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();

            $table->unique(
                ['school_id', 'academic_year_id', 'name'],
                'shule_terms_school_year_name_unique'
            );

            $table->foreign('school_id')
                ->references('id')
                ->on('shule_schools')
                ->cascadeOnDelete();

            $table->foreign('academic_year_id')
                ->references('id')
                ->on('shule_academic_years')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_terms');
    }
};