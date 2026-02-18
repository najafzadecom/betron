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
        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by_vendor_user_id')->nullable()->after('vendor_id');
            $table->foreign('created_by_vendor_user_id')->references('id')->on('vendor_users')->onDelete('set null');
            $table->index('created_by_vendor_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['created_by_vendor_user_id']);
            $table->dropIndex(['created_by_vendor_user_id']);
            $table->dropColumn('created_by_vendor_user_id');
        });
    }
};
