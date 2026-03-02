<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('k1_ledger_entries')) {
            return;
        }

        Schema::create('k1_ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignUuid('account_id')->constrained('k1_accounts')->restrictOnDelete();
            $table->string('reference_type', 50);
            $table->uuid('reference_id');
            $table->decimal('debit', 15, 2)->nullable();
            $table->decimal('credit', 15, 2)->nullable();
            $table->string('description');
            $table->timestamps();

            $table->index(['tenant_id', 'reference_type', 'reference_id'], 'k1_ledger_tenant_reference_idx');
            $table->index(['tenant_id', 'account_id'], 'k1_ledger_tenant_account_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('k1_ledger_entries');
    }
};
