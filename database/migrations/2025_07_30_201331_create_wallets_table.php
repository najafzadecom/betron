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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Wallet name');
            $table->string('iban');
            $table->float('total_amount')->default(0);
            $table->float('blocked_amount')->default(0);
            $table->float('maximum_amount')->default(0);
            $table->float('single_deposit_min_amount')->default(0);
            $table->float('single_deposit_max_amount')->default(0);
            $table->timestamp('last_sync_date')->nullable();
            $table->unsignedBigInteger('bank_id')->default(0);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->string('phone')->nullable();
            $table->string('mobile_banking_password')->nullable();
            $table->boolean('linked_card')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
