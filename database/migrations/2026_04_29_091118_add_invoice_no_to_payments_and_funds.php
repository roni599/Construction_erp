<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('client_payments', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->unique()->after('id');
        });

        Schema::table('manager_funds', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_payments', function (Blueprint $table) {
            $table->dropColumn('invoice_no');
        });

        Schema::table('manager_funds', function (Blueprint $table) {
            $table->dropColumn('invoice_no');
        });
    }
};
