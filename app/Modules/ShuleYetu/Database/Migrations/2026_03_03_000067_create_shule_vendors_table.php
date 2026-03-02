<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'name'], 'shule_vendors_school_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_vendors');
    }
};
