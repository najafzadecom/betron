<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->double('guarantee_limit')->default(0)->after('deposit_amount');
            $table->double('available_deposit_capacity')->default(0)->after('guarantee_limit');
        });

        // Existing vendors: treat current deposit as initial guarantee until admin adjusts.
        DB::table('vendors')->update([
            'guarantee_limit' => DB::raw('deposit_amount'),
            'available_deposit_capacity' => DB::raw('deposit_amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['guarantee_limit', 'available_deposit_capacity']);
        });
    }
};
