<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_stock_issues', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('shule_items')->restrictOnDelete();
            $table->decimal('qty', 14, 3);
            $table->date('issue_date');
            $table->string('issued_to', 150)->nullable();
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->decimal('total_cost', 14, 2)->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'item_id', 'issue_date'], 'shule_stock_issues_item_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_stock_issues');
    }
};
