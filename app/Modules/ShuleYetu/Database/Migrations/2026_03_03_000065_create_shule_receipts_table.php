<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('payment_id')->constrained('shule_payments')->cascadeOnDelete();
            $table->string('receipt_number', 100);
            $table->dateTime('issued_at');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'receipt_number'], 'shule_receipts_school_number_unique');
            $table->unique(['school_id', 'payment_id'], 'shule_receipts_school_payment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_receipts');
    }
};
