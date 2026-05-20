<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_active_created_at ON transactions (created_at DESC) WHERE deleted_at IS NULL AND status <> 0');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_status_created_at ON transactions (status, created_at DESC) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_wallet_created_at ON transactions (wallet_id, created_at DESC) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_vendor_created_at ON transactions (vendor_id, created_at DESC) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_order_id ON transactions (order_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_site_id ON transactions (site_id, created_at DESC) WHERE deleted_at IS NULL');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_withdrawals_created_at ON withdrawals (created_at DESC) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_withdrawals_vendor_created_at ON withdrawals (vendor_id, created_at DESC) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_withdrawals_status_created_at ON withdrawals (status, created_at DESC) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_withdrawals_order_id ON withdrawals (order_id) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_transactions_active_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_transactions_status_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_transactions_wallet_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_transactions_vendor_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_transactions_order_id');
        DB::statement('DROP INDEX IF EXISTS idx_transactions_site_id');

        DB::statement('DROP INDEX IF EXISTS idx_withdrawals_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_withdrawals_vendor_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_withdrawals_status_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_withdrawals_order_id');
    }
};
