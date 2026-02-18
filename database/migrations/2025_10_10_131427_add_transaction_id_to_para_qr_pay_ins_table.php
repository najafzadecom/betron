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
        Schema::table('para_qr_pay_ins', function (Blueprint $table) {
            $table->uuid('transaction_uuid')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('para_qr_pay_ins', function (Blueprint $table) {
            $table->dropColumn('transaction_uuid');
        });
    }
};
