<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shule_academic_years', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('school_id')->index();

            $table->string('name'); // e.g. 2026
            $table->date('starts_on');
            $table->date('ends_on');

            $table->boolean('is_active')->default(false);

            $table->timestamps();

            $table->foreign('school_id')
                  ->references('id')
                  ->on('schools')
                  ->cascadeOnDelete();

            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('shule_academic_years');
    }
};
