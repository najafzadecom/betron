<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * Kodda kullanılıyor: TransactionObserver (vendor_id), API Paypap (deposit_id),
     * TransactionScope filtreleri (vendor_id), admin Paypap durumu (deposit_id).
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')
                    ->default(0)
                    ->after('wallet_id')
                    ->comment('Wallet/vendor ilişkisi; API ve observer doldurur');
                $table->index('vendor_id');
            }

            if (!Schema::hasColumn('transactions', 'deposit_id')) {
                $table->string('deposit_id', 191)
                    ->nullable()
                    ->after('paid_status')
                    ->comment('Paypap bank deposit id (paypap akışı)');
                $table->index('deposit_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'deposit_id')) {
                $table->dropIndex(['deposit_id']);
                $table->dropColumn('deposit_id');
            }
            if (Schema::hasColumn('transactions', 'vendor_id')) {
                $table->dropIndex(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
        });
    }
};
