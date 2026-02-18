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
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Blacklisted user ID');
            $table->ipAddress('ip_address')->nullable()->comment('Blacklisted IP address');
            $table->string('reason')->nullable()->comment('Reason for blacklisting');
            $table->enum('type', ['user_id', 'ip_address'])->comment('Type of blacklist entry');
            $table->unsignedTinyInteger('site_id')->default(0)->comment('Site ID');
            $table->boolean('is_active')->default(true)->comment('Whether the blacklist entry is active');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['user_id', 'is_active']);
            $table->index(['ip_address', 'is_active']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
