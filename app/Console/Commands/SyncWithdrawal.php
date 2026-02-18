<?php

namespace App\Console\Commands;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use App\Payment\PratikPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SyncWithdrawal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-withdrawal';

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
        if (setting('withdrawal_status') != 1) {
            return 'Withdrawal is not enabled now';
        }

        $now = Carbon::now('Europe/Istanbul');
        $oneHourAgo = $now->copy()->subHours(1);
        $fiveMinuteAgo = $now->copy()->subMinutes(15);
        $sDate = $oneHourAgo->format('d-m-Y\TH:i:s');
        $lDate = $now->format('d-m-Y\TH:i:s');
        $transactionTypeId = 9;

        $withdrawals = $this->pratikPayment->moneyTransactionHistory($sDate, $lDate, $transactionTypeId);

        if ($withdrawals && isset($withdrawals->success) && $withdrawals->success && $withdrawals->ResponseCode == '0000' && $withdrawals->transactionList) {
            foreach ($withdrawals->transactionList as $withdrawal) {
                $receiverAccountHolderName = $withdrawal->receiverAccountHolderName;
                $receiverAccountHolderNameArray = explode(' ', $receiverAccountHolderName);
                $firstName = mb_strtolower($receiverAccountHolderNameArray[0]);
                $lastName = mb_strtolower($receiverAccountHolderNameArray[1]);
                $amount = abs(($withdrawal->transactionAmount / 100));

                DB::enableQueryLog();
                Withdrawal::query()
                    ->where('status', WithdrawalStatus::Processing)
                    ->whereRaw('LOWER(first_name) = ?', [$firstName])
                    ->whereRaw('LOWER(last_name) = ?', [$lastName])
                    ->where('currency', $withdrawal->currencyCode)
                    ->where('iban', $withdrawal->receiverIban)
                    ->where('order_id', $withdrawal->extTransactionId)
                    ->where('amount', $amount)
                    ->where('paid_status', false)
                    ->where('created_at', '<', $fiveMinuteAgo)
                    ->update([
                        'sender_iban' => $withdrawal->senderIban,
                        'sender_name' => $withdrawal->senderAccountHolderName,
                        'withdrawal_id' => $withdrawal->transactionId,
                        'status' => WithdrawalStatus::AutoConfirmed,
                        'paid_status' => true
                    ]);

                print_r(DB::getRawQueryLog());
            }
        }

    }
}
