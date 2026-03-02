<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_transport_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('shule_students')->cascadeOnDelete();
            $table->foreignUuid('route_id')->constrained('shule_routes')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('method', 50);
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('posted');
            $table->timestamp('paid_at');
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'paid_at'], 'shule_transport_payments_student_paid_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_transport_payments');
    }
};
