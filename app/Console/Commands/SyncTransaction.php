<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Payment\PratikPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private PratikPayment $pratikPayment;

    public function __construct(PratikPayment $pratikPayment)
    {
        parent::__construct();
        $this->pratikPayment = $pratikPayment;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now('Europe/Istanbul');
        $oneHourAgo = $now->copy()->subHour();
        $sDate = $oneHourAgo->format('d-m-Y\TH:i:s');
        $lDate = $now->format('d-m-Y\TH:i:s');

        $transactions = $this->pratikPayment->moneyTransactionHistory($sDate, $lDate);

        if ($transactions && isset($transactions->success) && $transactions->success && $transactions->ResponseCode == '0000' && $transactions->transactionList) {
            foreach ($transactions->transactionList as $transaction) {
                $senderAccountHolderName = $transaction->senderAccountHolderName;
                $senderAccountHolderNameArray = explode(' ', $senderAccountHolderName);
                $firstName = mb_strtolower($senderAccountHolderNameArray[0]);
                $lastName = mb_strtolower($senderAccountHolderNameArray[1]);

                $plus30 = $now->copy()->addMinutes(30)->setTimezone('Europe/Istanbul');

                Transaction::query()
                    ->where('status', 1)
                    ->whereRaw('LOWER(first_name) = ?', [$firstName])
                    ->whereRaw('LOWER(last_name) = ?', [$lastName])
                    ->where('currency', $transaction->currencyCode)
                    ->where('receiver_iban', $transaction->receiverIban)
                    ->where('amount', ($transaction->transactionAmount / 100))
                    ->where('created_at', '<', $plus30)
                    ->update([
                        'status' => TransactionStatus::AutoConfirmed,
                        'paid_status' => true
                    ]);
            }
        }

    }
}
