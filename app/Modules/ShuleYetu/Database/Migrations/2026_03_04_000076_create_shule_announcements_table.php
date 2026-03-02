<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shule_announcements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('shule_schools')->cascadeOnDelete();
            $table->string('title', 180);
            $table->text('body');
            $table->enum('audience', ['students', 'parents', 'staff', 'all']);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'audience', 'published_at'], 'shule_announcements_audience_published_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shule_announcements');
    }
};
