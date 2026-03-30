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
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'wallet_id')) {
                $table->unsignedBigInteger('wallet_id')->nullable()->after('user_id');
                $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('set null');
                $table->index('wallet_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawals', 'wallet_id')) {
                $table->dropForeign(['wallet_id']);
                $table->dropIndex(['wallet_id']);
                $table->dropColumn('wallet_id');
            }
        });
    }
};
