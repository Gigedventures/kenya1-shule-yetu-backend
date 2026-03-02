<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_payment_reversals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('payment_id')->constrained('shule_payments')->cascadeOnDelete();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->dateTime('reversed_at');
            $table->timestamps();

            $table->unique(['school_id', 'payment_id'], 'shule_payment_reversals_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_payment_reversals');
    }
};
