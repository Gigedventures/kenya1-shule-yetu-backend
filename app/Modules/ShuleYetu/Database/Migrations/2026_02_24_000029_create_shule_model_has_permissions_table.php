<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_model_has_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('permission_id')->constrained('shule_permissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['permission_id', 'user_id', 'school_id'], 'shule_mhp_perm_user_school_uq');
            $table->index('user_id', 'shule_mhp_user_idx');
            $table->index('school_id', 'shule_mhp_school_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_model_has_permissions');
    }
};
