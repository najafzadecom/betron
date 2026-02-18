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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Bank name');
            $table->text('image')->nullable()->comment('Bank logo file path');
            $table->unsignedTinyInteger('priority')->default(0)->comment('Bank sort order');
            $table->boolean('status')->default(false)->comment('Bank status');
            $table->boolean('transaction_status')->default(false)->comment('Bank transaction status');
            $table->boolean('withdrawal_status')->default(false)->comment('Bank withdrawal status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
