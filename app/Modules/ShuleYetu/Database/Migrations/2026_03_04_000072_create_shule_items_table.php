<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('shule_item_categories')->restrictOnDelete();
            $table->string('sku', 100);
            $table->string('name', 150);
            $table->string('unit', 40);
            $table->decimal('reorder_level', 14, 3)->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'sku'], 'shule_items_school_sku_unique');
            $table->index(['school_id', 'name'], 'shule_items_school_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_items');
    }
};
