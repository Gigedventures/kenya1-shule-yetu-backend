<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('staff_no', 100)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('staff_type', ['teacher', 'admin', 'support', 'driver', 'librarian', 'nurse'])->default('teacher');
            $table->foreignUuid('department_id')->nullable()->constrained('shule_departments')->nullOnDelete();
            $table->enum('status', ['active', 'suspended', 'left'])->default('active');
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'staff_no']);
            $table->index('user_id');
            $table->index('staff_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_staff');
    }
};
