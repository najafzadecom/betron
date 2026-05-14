<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CastBlacklistsUserIdToString extends Command
{
    protected $signature = 'blacklists:cast-user-id-to-string
                            {--force : Skip confirmation prompt}';

    protected $description = 'Alter blacklists.user_id to a nullable string column (one-off; not a migration).';

    public function handle(): int
    {
        if (! Schema::hasTable('blacklists')) {
            $this->error('Table "blacklists" does not exist.');

            return Command::FAILURE;
        }

        if (! Schema::hasColumn('blacklists', 'user_id')) {
            $this->error('Column "blacklists.user_id" does not exist.');

            return Command::FAILURE;
        }

        $type = Schema::getColumnType('blacklists', 'user_id');

        if (in_array($type, ['varchar', 'string', 'char', 'text'], true)) {
            $this->info("blacklists.user_id is already a string-compatible type ({$type}). Nothing to do.");

            return Command::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm(
            "This will alter blacklists.user_id from {$type} to VARCHAR(255) nullable. Continue?",
            false
        )) {
            $this->warn('Aborted.');

            return Command::FAILURE;
        }

        $this->dropUserIdActiveIndexIfPresent();

        Schema::table('blacklists', function (Blueprint $table) {
            $table->string('user_id', 255)
                ->nullable()
                ->comment('Blacklisted user ID')
                ->change();
        });

        $this->ensureUserIdActiveIndex();

        $this->info('Done: blacklists.user_id is now VARCHAR(255) nullable.');

        return Command::SUCCESS;
    }

    private function dropUserIdActiveIndexIfPresent(): void
    {
        try {
            Schema::table('blacklists', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'is_active']);
            });
            $this->info('Dropped composite index (user_id, is_active).');
        } catch (\Throwable $e) {
            $this->warn('Composite index drop skipped: '.$e->getMessage());
        }
    }

    private function ensureUserIdActiveIndex(): void
    {
        try {
            Schema::table('blacklists', function (Blueprint $table) {
                $table->index(['user_id', 'is_active']);
            });
            $this->info('Re-created composite index (user_id, is_active).');
        } catch (\Throwable $e) {
            $this->warn('Could not create composite index (may already exist): '.$e->getMessage());
        }
    }
}
