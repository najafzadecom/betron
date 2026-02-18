<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\TransactionLogService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

class CheckTransactionDraft extends Command
{
    /**
     * Method to display banner and logo
     */
    private function displayBanner(): void
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>╔═════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan;options=bold>║                                                 ║</>');
        $this->line('<fg=cyan;options=bold>║</>    <fg=white;bg=blue;options=bold>  EXPRESS BANK - TRANSACTION SYSTEM  </>        <fg=cyan;options=bold>║</>');
        $this->line('<fg=cyan;options=bold>║</>    <fg=yellow>       Transaction Status Check       </>       <fg=cyan;options=bold>║</>');
        $this->line('<fg=cyan;options=bold>║                                                 ║</>');
        $this->line('<fg=cyan;options=bold>╚═════════════════════════════════════════════════╝</>');
        $this->newLine();

        // Additional information
        $this->line(' <fg=gray>• Command:</> <fg=white;options=bold>app:check-transaction-draft</>');
        $this->line(' <fg=gray>• Time limit:</> <fg=white;options=bold>' . setting('draft_status_interval', 15) . ' minutes</>');
        $this->line(' <fg=gray>• Execution date:</> <fg=white;options=bold>' . now()->format('d.m.Y H:i:s') . '</>');
        $this->newLine();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-transaction-draft';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes transactions that have been in Processing status for more than 15 minutes back to Draft status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Display banner and logo
        $this->displayBanner();

        try {
            // Log to both standard log and transaction log service
            TransactionLogService::info('CheckTransactionDraft command started', [
                'command' => $this->signature,
                'execution_time' => now()->toDateTimeString(),
            ]);

            // Get the interval from settings or use default value (15 minutes)
            $minutesAgo = setting('draft_status_interval', 15);

            // Using chunk for performance and selecting only the necessary columns
            $query = Transaction::query()
                ->select(['id', 'status', 'created_at'])
                ->where('status', TransactionStatus::Processing)
                ->where('created_at', '<', now()->subMinutes($minutesAgo));

            $count = $query->count();

            if ($count > 0) {
                TransactionLogService::info("Found {$count} transactions in Processing status", [
                    'count' => $count,
                    'status' => TransactionStatus::Processing->value,
                    'minutes_ago' => $minutesAgo,
                ]);

                // Using chunk for processing large datasets
                $updatedCount = 0;
                $errorCount = 0;

                // Creating a progress bar
                $progressBar = $this->output->createProgressBar($count);
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
                $progressBar->start();

                $query->chunkById(100, function ($transactions) use (&$updatedCount, &$errorCount, $progressBar) {
                    TransactionLogService::logBatchProcessing($transactions->count(), "Processing transactions with chunk");

                    foreach ($transactions as $transaction) {
                        try {
                            $transaction->update(['status' => TransactionStatus::Draft]);
                            $updatedCount++;

                            // Advancing the progress bar
                            $progressBar->advance();

                            if ($updatedCount % 50 == 0) {
                                if ($updatedCount % 100 == 0) {
                                    TransactionLogService::info("{$updatedCount} transactions updated", [
                                        'updated_count' => $updatedCount,
                                        'total_count' => $count,
                                        'progress_percentage' => round(($updatedCount / $count) * 100, 2),
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            $errorCount++;
                            // Advancing progress bar even on error
                            $progressBar->advance();

                            TransactionLogService::error("Error updating transaction #{$transaction->id}", [
                                'transaction_id' => $transaction->id,
                                'error' => $e->getMessage(),
                                'old_status' => TransactionStatus::Processing->value,
                                'new_status' => TransactionStatus::Draft->value,
                            ]);
                        }
                    }
                });

                // Finishing the progress bar
                $progressBar->finish();
                $this->newLine(2);

                // Log completion with success rate
                $successRate = $count > 0 ? round(($updatedCount / $count) * 100, 2) : 100;
                $logLevel = $errorCount > 0 ? 'warning' : 'info';

                TransactionLogService::{$logLevel}("Transaction status update completed", [
                    'total' => $count,
                    'updated' => $updatedCount,
                    'errors' => $errorCount,
                    'success_rate' => $successRate,
                    'execution_time_seconds' => $progressBar->getStartTime() ? round(microtime(true) - $progressBar->getStartTime(), 2) : 0,
                    'status_from' => TransactionStatus::Processing->value,
                    'status_to' => TransactionStatus::Draft->value,
                ]);

                // Report in table format
                $this->line("\n<fg=blue;options=bold>Operation Report:</>");

                $table = new Table($this->output);
                $table->setHeaders(['Parameter', 'Value', 'Status']);

                $table->addRow(['Total transactions', $count, '']);

                $successRate = $count > 0 ? round(($updatedCount / $count) * 100, 2) : 0;
                $statusIcon = $successRate >= 90 ? '<fg=green>✓</>' : ($successRate >= 50 ? '<fg=yellow>⚠</>' : '<fg=red>✗</>');
                $table->addRow(['Updated transactions', $updatedCount, "{$statusIcon} {$successRate}%"]);

                $errorStatus = $errorCount == 0 ? '<fg=green>✓ No errors</>' : "<fg=red>✗ {$errorCount} errors</>";
                $table->addRow(['Error count', $errorCount, $errorStatus]);

                $table->addRow(['Processing time', $progressBar->getStartTime() ? round(microtime(true) - $progressBar->getStartTime(), 2) . ' sec' : '-', '']);

                $table->setStyle('box');
                $table->render();

                $this->newLine();
            } else {
                TransactionLogService::info('No transactions to update', [
                    'status' => TransactionStatus::Processing->value,
                    'minutes_ago' => $minutesAgo,
                ]);
                $this->line("\n<fg=yellow;options=bold>Attention:</> <fg=white>No old transactions found in Processing status</>");
            }

            TransactionLogService::info('CheckTransactionDraft command completed successfully');
            $this->line("\n<fg=green;options=bold>✓ Operation completed successfully!</>");
            return 0;
        } catch (\Exception $e) {

            // Log critical error to transaction log service which will send to Telegram/Slack
            TransactionLogService::critical('CheckTransactionDraft command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'command' => $this->signature,
            ]);

            $this->line("\n<fg=red;options=bold>✗ Error occurred!</>");
            $this->error('  ' . $e->getMessage());
            return 1;
        }
    }
}
