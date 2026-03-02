<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {

            $table->text('description')->nullable()->after('name');

            $table->string('coming_soon_message')->nullable()->after('description');

            $table->string('image')->nullable()->after('coming_soon_message');

            $table->integer('display_order')->default(0)->after('image');

        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {

            $table->dropColumn([
                'description',
                'coming_soon_message',
                'image',
                'display_order'
            ]);

        });
    }
};
