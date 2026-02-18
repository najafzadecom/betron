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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Website name');
            $table->string('url')->nullable()->comment('Website URL');
            $table->text('description')->nullable()->comment('Website description');
            $table->string('logo')->nullable()->comment('Logo for website');
            $table->text('token')->nullable()->comment('Website token');
            $table->unsignedTinyInteger('transaction_fee')->default(0);
            $table->unsignedTinyInteger('withdrawal_fee')->default(0);
            $table->unsignedTinyInteger('settlement_fee')->default(0);
            $table->boolean('status')->default(false)->comment('Website status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
