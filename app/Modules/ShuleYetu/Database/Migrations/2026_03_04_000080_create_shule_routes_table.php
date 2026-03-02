<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_routes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->decimal('fee_amount', 14, 2)->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'name'], 'shule_routes_school_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_routes');
    }
};
