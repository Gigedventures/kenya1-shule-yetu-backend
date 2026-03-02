<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_payment_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('payment_id')->constrained('shule_payments')->cascadeOnDelete();
            $table->foreignUuid('student_bill_id')->constrained('shule_student_bills')->cascadeOnDelete();
            $table->decimal('allocated_amount', 12, 2);
            $table->timestamps();

            $table->unique(['school_id', 'payment_id', 'student_bill_id'], 'shule_payment_allocations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_payment_allocations');
    }
};
