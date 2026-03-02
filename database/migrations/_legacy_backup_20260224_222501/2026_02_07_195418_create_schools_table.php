<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('owner_user_id')->index();
            $table->string('name');
            $table->string('code')->unique();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('location')->nullable();

            $table->string('currency')->default('KES');
            $table->string('country')->default('KE');

            $table->enum('status', ['pending','active','suspended'])->default('pending');
            $table->timestamp('activated_at')->nullable();

            $table->timestamps();

            $table->foreign('owner_user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('schools');
    }
};
