<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_driver_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('vehicle_id')->constrained('shule_vehicles')->cascadeOnDelete();
            $table->foreignUuid('staff_id')->constrained('shule_staff')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'vehicle_id', 'start_date'], 'shule_driver_assignments_vehicle_start_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_driver_assignments');
    }
};
