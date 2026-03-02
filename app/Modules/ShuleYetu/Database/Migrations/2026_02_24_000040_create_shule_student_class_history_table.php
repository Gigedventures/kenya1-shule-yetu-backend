<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_student_class_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('shule_students')->cascadeOnDelete();
            $table->foreignUuid('from_class_id')->nullable()->constrained('shule_classes')->nullOnDelete();
            $table->foreignUuid('from_stream_id')->nullable()->constrained('shule_streams')->nullOnDelete();
            $table->foreignUuid('to_class_id')->nullable()->constrained('shule_classes')->nullOnDelete();
            $table->foreignUuid('to_stream_id')->nullable()->constrained('shule_streams')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->string('reason')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('student_id');
            $table->index('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_student_class_history');
    }
};

