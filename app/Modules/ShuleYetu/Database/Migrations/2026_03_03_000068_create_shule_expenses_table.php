<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('financial_year_id')->constrained('shule_financial_years')->restrictOnDelete();
            $table->foreignUuid('category_id')->constrained('shule_expense_categories')->restrictOnDelete();
            $table->foreignUuid('vendor_id')->nullable()->constrained('shule_vendors')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->date('expense_date');
            $table->enum('status', ['draft', 'approved', 'posted'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_expenses');
    }
};
