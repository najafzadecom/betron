<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_daily_reconciliations', function (Blueprint $table) {
            $table->double('y_komisyon_oran')->default(4)->after('man_cekim');
            $table->double('t_komisyon_oran')->default(4)->after('teslimat');
        });

        DB::table('vendor_daily_reconciliations')->update([
            'y_komisyon_oran' => 4,
            't_komisyon_oran' => 4,
        ]);
    }

    public function down(): void
    {
        Schema::table('vendor_daily_reconciliations', function (Blueprint $table) {
            $table->dropColumn(['y_komisyon_oran', 't_komisyon_oran']);
        });
    }
};
