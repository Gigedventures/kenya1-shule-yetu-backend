<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shule_streams', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('class_id')->index();

            $table->string('name'); // A, B, Blue, East
            $table->unsignedSmallInteger('capacity')->nullable();

            $table->timestamps();

            $table->foreign('school_id')
                  ->references('id')
                  ->on('schools')
                  ->cascadeOnDelete();

            $table->foreign('class_id')
                  ->references('id')
                  ->on('shule_classes')
                  ->cascadeOnDelete();

            $table->unique(['class_id', 'name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('shule_streams');
    }
};
