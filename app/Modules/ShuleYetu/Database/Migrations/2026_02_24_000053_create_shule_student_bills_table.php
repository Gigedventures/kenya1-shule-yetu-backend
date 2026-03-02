<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_student_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('shule_students')->cascadeOnDelete();
            $table->foreignUuid('fee_structure_id')->constrained('shule_fee_structures')->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'fee_structure_id'], 'shule_student_bills_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_student_bills');
    }
};
