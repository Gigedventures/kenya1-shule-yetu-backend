<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shule_payments', function (Blueprint $table) {
            $table->string('idempotency_key', 191)->nullable()->after('reference');
            $table->unique(['school_id', 'idempotency_key'], 'shule_payments_school_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::table('shule_payments', function (Blueprint $table) {
            $table->dropUnique('shule_payments_school_idempotency_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
