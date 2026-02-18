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
        Schema::create('para_qr_pay_outs', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_order_no');
            $table->string('receiver_full_name');
            $table->string('receiver_iban');
            $table->text('description')->nullable();
            $table->float('amount');
            $table->text('response')->nullable();
            $table->uuid('system_order_no')->nullable();
            $table->string('payout_message')->nullable();
            $table->string('message')->nullable();
            $table->text('callback_response')->nullable();
            $table->string('hash')->nullable();
            $table->string('reason')->nullable();
            $table->unsignedTinyInteger('status')->nullable();
            $table->unsignedTinyInteger('direction')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('para_qr_pay_outs');
    }
};
