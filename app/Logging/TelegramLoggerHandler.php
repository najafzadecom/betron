<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Http;

class TelegramLoggerHandler extends AbstractProcessingHandler
{
    protected string $token;
    protected string $chatId;

    public function __construct($level = 'debug', array $config = [])
    {
        parent::__construct($level);

        // Laravel 9+ passes level as the first param and config as the second
        if (empty($config) && is_array($level)) {
            $config = $level;
            $level = $config['level'] ?? 'debug';
            parent::__construct($level);
        }

        $this->token = $config['token'] ?? env('TELEGRAM_BOT_TOKEN');
        $this->chatId = $config['chat_id'] ?? env('TELEGRAM_CHAT_ID');

        if (empty($this->token) || empty($this->chatId)) {
            \Log::error('TelegramLoggerHandler initialized with missing token or chat_id', [
                'token_exists' => !empty($this->token),
                'chat_id_exists' => !empty($this->chatId)
            ]);
        }
    }

    /**
     * @param LogRecord $record
     */
    protected function write(LogRecord $record): void
    {
        if (empty($this->token) || empty($this->chatId)) {
            \Log::error('Telegram logger: Missing token or chat_id', [
                'token_exists' => !empty($this->token),
                'chat_id_exists' => !empty($this->chatId),
            ]);
            return;
        }

        $formattedMessage = $this->formatMessage($record);

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $formattedMessage,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            // Log the response for debugging
            if (!$response->successful()) {
                \Log::error('Telegram API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $this->chatId,
                    'token_length' => strlen($this->token),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception while sending Telegram message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Format the log message for Telegram
     */
    protected function formatMessage(LogRecord $record): string
    {
        $level = strtoupper($record->level->name);
        $emoji = $this->getLevelEmoji($level);

        $message = "<b>{$emoji} {$level}</b>\n";
        $message .= "<b>Message:</b> " . $this->escapeHtml($record->message) . "\n";

        // Add timestamp
        $message .= "<b>Time:</b> " . date('Y-m-d H:i:s') . "\n";

        // Add context data if available
        if (!empty($record->context)) {
            $message .= "\n<b>Context:</b>\n";
            foreach ($record->context as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                } else {
                    $value = (string) $value;
                }
                if ($key === 'trace' && strlen($value) > 300) {
                    $value = substr($value, 0, 300) . '...';
                }
                $message .= "<i>" . $this->escapeHtml($key) . ":</i> " . $this->escapeHtml($value) . "\n";
            }
        }

        return $message;
    }

    /**
     * Escape HTML entities for Telegram HTML parse mode
     * Telegram requires escaping: <, >, &
     */
    protected function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Get emoji for log level
     */
    protected function getLevelEmoji(string $level): string
    {
        return match ($level) {
            'DEBUG' => '🔍',
            'INFO' => 'ℹ️',
            'NOTICE' => '📝',
            'WARNING' => '⚠️',
            'ERROR' => '❌',
            'CRITICAL' => '🔥',
            'ALERT' => '🚨',
            'EMERGENCY' => '💀',
            default => '📊',
        };
    }
}
