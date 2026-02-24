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
        Schema::table('sites', function (Blueprint $table) {
            // Use decimal(5,2) to allow values like 12.34 (%)
            $table->decimal('transaction_fee', 5, 2)->default(0)->change();
            $table->decimal('withdrawal_fee', 5, 2)->default(0)->change();
            $table->decimal('settlement_fee', 5, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->unsignedTinyInteger('transaction_fee')->default(0)->change();
            $table->unsignedTinyInteger('withdrawal_fee')->default(0)->change();
            $table->unsignedTinyInteger('settlement_fee')->default(0)->change();
        });
    }
};

