<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shule_terms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('academic_year_id')->index();

            $table->string('name'); // Term 1, Term 2, Term 3
            $table->date('starts_on');
            $table->date('ends_on');

            $table->boolean('is_active')->default(false);

            $table->timestamps();

            $table->foreign('school_id')
                  ->references('id')
                  ->on('schools')
                  ->cascadeOnDelete();

            $table->foreign('academic_year_id')
                  ->references('id')
                  ->on('shule_academic_years')
                  ->cascadeOnDelete();

            $table->unique(['academic_year_id', 'name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('shule_terms');
    }
};
