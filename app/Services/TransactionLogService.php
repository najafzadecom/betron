<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionLogService
{
    /**
     * Log a debug message to transaction logs
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }

    /**
     * Log an info message to transaction logs
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    /**
     * Log a warning message to transaction logs
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    /**
     * Log an error message to transaction logs
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    /**
     * Log a critical message to transaction logs
     */
    public static function critical(string $message, array $context = []): void
    {
        self::log('critical', $message, $context);
    }

    /**
     * Log a message to transaction logs
     */
    protected static function log(string $level, string $message, array $context = []): void
    {
        // Add request ID to context for tracking
        $context['request_id'] = $context['request_id'] ?? Str::uuid()->toString();

        // Add timestamp
        $context['timestamp'] = now()->format('Y-m-d H:i:s');

        // Log to a transaction file
        Log::channel('transactions')->{$level}($message, $context);

        // Also log to Telegram if the level is warning or higher
        try {
            // Check if we should send this message to Telegram based on level
            $shouldSendToTelegram = in_array($level, ['warning', 'error', 'critical', 'alert', 'emergency', 'info']);

            if ($shouldSendToTelegram) {
                Log::channel('telegram')->{$level}($message, $context);
            }
        } catch (\Exception $e) {
            Log::error('Error sending to Telegram', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Log transaction status change
     */
    public static function logStatusChange(
        $transaction,
        $oldStatus,
        $newStatus,
        string $message = null
    ): void {
        $context = [
            'transaction_id' => $transaction->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ];

        if (!$message) {
            $message = "Transaction #{$transaction->id} status changed from {$oldStatus} to {$newStatus}";
        }

        self::info($message, $context);
    }

    /**
     * Log transaction processing batch
     */
    public static function logBatchProcessing(int $count, string $message = null): void
    {
        $context = [
            'count' => $count,
            'batch_id' => Str::uuid()->toString(),
        ];

        if (!$message) {
            $message = "Processing batch of {$count} transactions";
        }

        self::info($message, $context);
    }
}
