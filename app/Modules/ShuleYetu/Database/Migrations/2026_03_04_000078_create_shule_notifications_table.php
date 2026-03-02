<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 120);
            $table->json('payload');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'user_id', 'read_at'], 'shule_notifications_user_read_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_notifications');
    }
};
