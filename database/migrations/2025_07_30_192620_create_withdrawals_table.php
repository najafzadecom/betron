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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->unsignedBigInteger('user_id')->default(0)->comment('User ID of the withdrawal owner');
//            $table->unsignedBigInteger('wallet_id')->default(0);
//            $table->string('sender_name')->nullable();
//            $table->string('sender_iban')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedBigInteger('bank_id')->default(0)->comment('ID of the bank associated with the transaction');
            $table->string('bank_name')->comment('ID of the bank associated with the transaction');
            $table->string('iban')->comment('IBAN of the withdraw request owner');
            $table->float('amount')->default(0);
            $table->unsignedTinyInteger('fee')->default(0);
            $table->float('fee_amount')->default(0);
            $table->string('order_id')->comment('Order ID for the withdrawal request');
            $table->string('currency', 3)->default('TRY');
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedTinyInteger('site_id')->default(0);
            $table->boolean('paid_status')->default(false);
            $table->boolean('manual')->default(true);
            $table->unsignedBigInteger('vendor_id')->default(0);
            $table->dateTime('accepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
