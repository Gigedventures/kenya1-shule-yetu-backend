<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_staff_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('staff_id')->constrained('shule_staff')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'staff_id', 'attendance_date'], 'shule_staff_attendance_unique');
            $table->index('attendance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_staff_attendance');
    }
};
