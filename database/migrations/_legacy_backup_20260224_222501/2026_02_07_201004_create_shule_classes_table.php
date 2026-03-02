<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shule_classes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('school_id')->index();
            $table->string('name'); // e.g. Grade 1, Form 1
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('school_id')
                  ->references('id')
                  ->on('schools')
                  ->cascadeOnDelete();

            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('shule_classes');
    }
};
