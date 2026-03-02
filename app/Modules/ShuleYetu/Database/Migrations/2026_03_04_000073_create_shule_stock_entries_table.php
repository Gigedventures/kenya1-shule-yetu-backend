<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_stock_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('shule_items')->restrictOnDelete();
            $table->foreignUuid('vendor_id')->nullable()->constrained('shule_vendors')->nullOnDelete();
            $table->decimal('qty', 14, 3);
            $table->decimal('unit_cost', 14, 2);
            $table->date('entry_date');
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'item_id', 'entry_date'], 'shule_stock_entries_item_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_stock_entries');
    }
};
