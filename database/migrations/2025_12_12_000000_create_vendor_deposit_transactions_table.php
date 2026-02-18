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
        Schema::create('vendor_deposit_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->enum('type', ['add', 'subtract', 'transaction', 'withdrawal'])->comment('Transaction type: add, subtract, transaction (decreases), withdrawal (increases)');
            $table->double('amount')->comment('Transaction amount');
            $table->double('previous_balance')->default(0)->comment('Balance before transaction');
            $table->double('new_balance')->default(0)->comment('Balance after transaction');
            $table->text('note')->nullable()->comment('Optional note for the transaction');
            $table->unsignedBigInteger('created_by')->nullable()->comment('User who created this transaction');
            $table->unsignedBigInteger('transaction_id')->nullable()->comment('Related transaction ID if type is transaction');
            $table->unsignedBigInteger('withdrawal_id')->nullable()->comment('Related withdrawal ID if type is withdrawal');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            $table->foreign('withdrawal_id')->references('id')->on('withdrawals')->onDelete('set null');
            $table->index('vendor_id');
            $table->index('type');
            $table->index('created_at');
            $table->index('transaction_id');
            $table->index('withdrawal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_deposit_transactions');
    }
};

