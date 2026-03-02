<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 100)->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'name']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_departments');
    }
};
