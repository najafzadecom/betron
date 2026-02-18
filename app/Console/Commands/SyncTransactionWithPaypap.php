<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Payment\Paypap;
use Illuminate\Console\Command;

class SyncTransactionWithPaypap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-transaction-with-paypap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private Paypap $paypap;

    public function __construct(Paypap $paypap)
    {
        parent::__construct();
        $this->paypap = $paypap;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transactions = Transaction::query()
            ->where('status', TransactionStatus::Pending)
            ->whereRaw('paid_status IS FALSE')
            ->whereNotNull('deposit_id')
            ->whereNotNull('receiver_iban')
            ->where('payment_method', 'paypap')
            ->get();

        if ($transactions->isNotEmpty()) {
            foreach ($transactions as $transaction) {
                $result = $this->paypap->getBankDeposit($transaction->deposit_id);

                if (isset($result['data']) && $result['data']['status']['code'] == '4001' && $result['data']['requestedAmount'] == $result['data']['amount']) {
                    $transaction->paid_status = true;
                    $transaction->status = TransactionStatus::AutoConfirmed;
                    $transaction->save();
                } elseif (isset($result['error']) && $result['error']['status']['code'] > 4001) {
                    $transaction->paid_status = false;
                    $transaction->status = TransactionStatus::AutoCancelled;
                    $transaction->save();
                }
            }
        }
    }
}
