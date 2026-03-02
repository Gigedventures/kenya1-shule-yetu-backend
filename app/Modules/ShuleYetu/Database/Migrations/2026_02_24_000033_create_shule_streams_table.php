<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_streams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('shule_classes')->cascadeOnDelete();
            $table->string('name', 100);
            $table->integer('capacity')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'class_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_streams');
    }
};
