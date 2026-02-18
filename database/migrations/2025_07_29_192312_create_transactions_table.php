<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedBigInteger('user_id')->default(0)->comment('User ID of the transaction owner');
            $table->string('first_name')->comment('First name of the transaction owner');
            $table->string('last_name')->comment('Last name of the transaction owner');
            $table->string('phone')->nullable()->comment('Phone number of the transaction owner');
            $table->float('amount')->default(0);
            $table->unsignedTinyInteger('fee')->default(0);
            $table->float('fee_amount')->default(0);
            $table->string('order_id')->comment('Order ID for the transaction request');
            $table->string('currency', 3)->default('TRY');
            $table->unsignedBigInteger('wallet_id')->default(0)->comment('ID of the wallet associated with the transaction');
            $table->string('receiver_name')->nullable()->comment('Wallet account name of the transaction');
            $table->string('receiver_iban')->nullable()->comment('ID of the wallet iban associated with the transaction');
            $table->unsignedBigInteger('bank_id')->default(0)->comment('ID of the bank associated with the transaction');
            $table->ipAddress('client_ip')->comment('IP address of the client making the transaction');
            $table->unsignedTinyInteger('site_id')->default(0)->comment('ID of the site associated with the transaction');
            $table->string('site_name')->nullable()->comment('Name of the site associated with the transaction');
            $table->tinyInteger('status')->default(0);
            $table->boolean('paid_status')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
