<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_grade_bands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->string('grade');
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'min_percentage', 'max_percentage'], 'shule_grade_bands_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_grade_bands');
    }
};
