<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shule_student_bills', function (Blueprint $table) {
            $table->string('invoice_number', 100)->nullable()->after('fee_structure_id');
            $table->dateTime('issued_at')->nullable()->after('invoice_number');
            $table->date('due_date')->nullable()->after('issued_at');
            $table->enum('invoice_status', ['draft', 'issued', 'void'])->default('issued')->after('due_date');

            $table->unique(['school_id', 'invoice_number'], 'shule_student_bills_invoice_unique');
        });
    }

    public function down(): void
    {
        Schema::table('shule_student_bills', function (Blueprint $table) {
            $table->dropUnique('shule_student_bills_invoice_unique');
            $table->dropColumn(['invoice_number', 'issued_at', 'due_date', 'invoice_status']);
        });
    }
};
