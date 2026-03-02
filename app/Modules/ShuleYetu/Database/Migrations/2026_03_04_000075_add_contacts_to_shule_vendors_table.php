<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shule_vendors', function (Blueprint $table): void {
            if (!Schema::hasColumn('shule_vendors', 'contacts')) {
                $table->json('contacts')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shule_vendors', function (Blueprint $table): void {
            if (Schema::hasColumn('shule_vendors', 'contacts')) {
                $table->dropColumn('contacts');
            }
        });
    }
};
