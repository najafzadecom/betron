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
        Schema::create('pratik_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id')->unique();
            $table->string('walletId')->unique();
            $table->unsignedBigInteger('totalBalance')->default(0);
            $table->unsignedBigInteger('unavailableBalance')->default(0);
            $table->unsignedBigInteger('dailyIncomingLimit')->default(0);
            $table->unsignedBigInteger('dailyOutgoingLimit')->default(0);
            $table->string('iban')->nullable();
            $table->string('bankName')->nullable();
            $table->string('currencyCode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pratik_wallets');
    }
};
