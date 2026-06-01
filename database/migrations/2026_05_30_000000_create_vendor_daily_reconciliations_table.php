<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_daily_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->date('reconciliation_date');
            $table->double('devir')->default(0);
            $table->double('yatirim')->default(0);
            $table->double('man_yatirim')->default(0);
            $table->double('cekim')->default(0);
            $table->double('man_cekim')->default(0);
            $table->double('y_komisyon')->default(0);
            $table->double('teslimat')->default(0);
            $table->double('t_komisyon')->default(0);
            $table->double('kalan')->default(0);
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['vendor_id', 'reconciliation_date']);
            $table->index(['vendor_id', 'reconciliation_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_daily_reconciliations');
    }
};
