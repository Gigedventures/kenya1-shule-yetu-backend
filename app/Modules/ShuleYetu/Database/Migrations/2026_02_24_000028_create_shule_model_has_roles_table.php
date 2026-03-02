<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_model_has_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('role_id')->constrained('shule_roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'user_id', 'school_id'], 'shule_mhr_role_user_school_uq');
            $table->index('user_id', 'shule_mhr_user_idx');
            $table->index('school_id', 'shule_mhr_school_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_model_has_roles');
    }
};
