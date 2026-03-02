<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->string('admission_no', 100)->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('nationality')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'transferred', 'suspended'])->default('active');
            $table->foreignUuid('current_class_id')->nullable()->constrained('shule_classes')->nullOnDelete();
            $table->foreignUuid('current_stream_id')->nullable()->constrained('shule_streams')->nullOnDelete();
            $table->date('admission_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'admission_no']);
            $table->index('current_class_id');
            $table->index('current_stream_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_students');
    }
};
