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
        Schema::create('site_statistics', function (Blueprint $table) {
            $table->unsignedBigInteger('site_id');
            $table->double('pay_in_total')->default(0);
            $table->double('pay_in_fee_total')->default(0);
            $table->double('pay_in_grand_total')->default(0);
            $table->double('pay_out_total')->default(0);
            $table->double('pay_out_fee_total')->default(0);
            $table->double('pay_out_grand_total')->default(0);
            $table->double('total')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_statistics');
    }
};
