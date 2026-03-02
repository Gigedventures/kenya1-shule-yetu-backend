<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_vehicles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->string('plate_no', 40);
            $table->unsignedInteger('capacity');
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['school_id', 'plate_no'], 'shule_vehicles_school_plate_no_unique');
            $table->index(['school_id', 'status'], 'shule_vehicles_school_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_vehicles');
    }
};
