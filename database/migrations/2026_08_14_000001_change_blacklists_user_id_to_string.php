<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('blacklists') || ! Schema::hasColumn('blacklists', 'user_id')) {
            return;
        }

        $type = Schema::getColumnType('blacklists', 'user_id');

        if (in_array($type, ['varchar', 'string', 'char', 'text'], true)) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE blacklists ALTER COLUMN user_id TYPE VARCHAR(255) USING user_id::text');
            DB::statement("COMMENT ON COLUMN blacklists.user_id IS 'Blacklisted user ID'");

            return;
        }

        Schema::table('blacklists', function (Blueprint $table) {
            $table->string('user_id', 255)
                ->nullable()
                ->comment('Blacklisted user ID')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('blacklists') || ! Schema::hasColumn('blacklists', 'user_id')) {
            return;
        }

        $type = Schema::getColumnType('blacklists', 'user_id');

        if (! in_array($type, ['varchar', 'string', 'char', 'text'], true)) {
            return;
        }

        Schema::table('blacklists', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->comment('Blacklisted user ID')
                ->change();
        });
    }
};
