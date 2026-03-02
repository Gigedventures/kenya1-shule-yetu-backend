<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_student_guardian', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('shule_students')->cascadeOnDelete();
            $table->foreignUuid('guardian_id')->constrained('shule_guardians')->cascadeOnDelete();
            $table->enum('relationship', ['father', 'mother', 'guardian', 'sponsor', 'other'])->default('guardian');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'guardian_id']);
            $table->index('student_id');
            $table->index('guardian_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_student_guardian');
    }
};

