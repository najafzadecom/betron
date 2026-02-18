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
        Schema::table('vendors', function (Blueprint $table) {
            $table->double('minimum_withdrawal_amount')->nullable()->default(0)->after('withdrawal_enabled');
            $table->double('maximum_withdrawal_amount')->nullable()->default(0)->after('minimum_withdrawal_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['minimum_withdrawal_amount', 'maximum_withdrawal_amount']);
        });
    }
};
