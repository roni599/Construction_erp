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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('nid');
            $table->string('nid_frontend')->nullable()->after('address');
            $table->string('nid_backend')->nullable()->after('nid_frontend');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nid')->nullable()->after('address');
            $table->dropColumn('nid_frontend');
            $table->dropColumn('nid_backend');
        });
    }
};
