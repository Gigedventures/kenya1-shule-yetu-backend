<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_class_subject', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('shule_classes')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('shule_subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'class_id', 'subject_id']);
            $table->index('class_id');
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_class_subject');
    }
};

