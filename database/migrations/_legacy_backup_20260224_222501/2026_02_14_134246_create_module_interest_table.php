<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_interest', function (Blueprint $table) {
            $table->id();
            $table->string('module_slug');
            $table->string('name')->nullable();
            $table->string('contact'); // phone or email
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_interest');
    }
};
