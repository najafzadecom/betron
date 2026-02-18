<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteOldPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-pending-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete pending transactions older than 30 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thirtyMinutesAgo = Carbon::now('Europe/Istanbul')->subMinutes(30);

        $transactions = Transaction::query()
            ->where('status', TransactionStatus::Pending)
            ->where('created_at', '<', $thirtyMinutesAgo)
            ->get();

        $count = $transactions->count();

        if ($count === 0) {
            $this->info('No pending transactions older than 30 minutes found.');
            return Command::SUCCESS;
        }

        $deleted = 0;
        foreach ($transactions as $transaction) {
            $transaction->forceDelete();
            $deleted++;
        }

        $this->info("Successfully deleted {$deleted} pending transaction(s) older than 30 minutes.");

        return Command::SUCCESS;
    }
}
