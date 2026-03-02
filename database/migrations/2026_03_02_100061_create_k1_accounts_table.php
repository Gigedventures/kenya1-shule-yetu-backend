<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('k1_accounts')) {
            return;
        }

        Schema::create('k1_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->uuid('parent_id')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'k1_accounts_tenant_code_unique');
            $table->foreign('parent_id')->references('id')->on('k1_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('k1_accounts');
    }
};
