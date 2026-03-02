<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('shule_stream_id')
                  ->nullable()
                  ->after('shule_class_id')
                  ->constrained('shule_streams')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['shule_stream_id']);
            $table->dropColumn('shule_stream_id');
        });
    }
};