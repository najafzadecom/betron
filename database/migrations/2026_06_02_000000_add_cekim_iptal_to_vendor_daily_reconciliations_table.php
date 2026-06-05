<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('vendor_daily_reconciliations', function (Blueprint $table) {
            $table->double('cekim_iptal')->default(0)->after('man_cekim');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_daily_reconciliations', function (Blueprint $table) {
            $table->dropColumn('cekim_iptal');
        });
    }
};
