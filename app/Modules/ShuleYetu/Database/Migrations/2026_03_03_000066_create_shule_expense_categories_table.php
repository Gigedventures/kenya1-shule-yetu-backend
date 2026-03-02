<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_expense_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('expense_account_code', 50)->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'name'], 'shule_expense_categories_school_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_expense_categories');
    }
};
