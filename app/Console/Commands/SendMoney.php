<?php

namespace App\Console\Commands;

use App\Enums\WithdrawalStatus;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Payment\PratikPayment;
use Illuminate\Console\Command;

class SendMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-money';

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

        $wallet_id = 14;
        $wallet = Wallet::query()->with('pratikWallet')->find($wallet_id);

        if ($wallet->pratikWallet && $wallet->pratikWallet->walletId) {
            $withdrawals = Withdrawal::query()->where('status', WithdrawalStatus::Pending)->get();


            if ($withdrawals->isNotEmpty()) {

                $passCode = $this->pratikPayment->myConfirmKey();


                foreach ($withdrawals as $withdrawal) {
                    $walletId = $wallet->pratikWallet?->walletId;

                    $receiver = $withdrawal->receiver;
                    $iban = $withdrawal->iban;
                    $amount = $withdrawal->amount * 100;
                    $extTransactionId = $withdrawal->order_id;
                    $passCodeEncoded = base64_encode($passCode);


                    $transaction = $this->pratikPayment->sendMoneyToBank($walletId, $receiver, $iban, $amount, $extTransactionId);

                    if ($transaction && isset($transaction->Success) && $transaction->Success && $transaction->ResponseCode == '0000' && $transaction->transactionDetails) {
                        $transactionId = $transaction->transactionDetails[0]->transactionId;
                        $sendMoneyConfirm = $this->pratikPayment->sendMoneyConfirm($walletId, $amount, $transactionId, $passCodeEncoded);

                        if ($sendMoneyConfirm && isset($sendMoneyConfirm->Success) && $sendMoneyConfirm->Success && $sendMoneyConfirm->ResponseCode == '0000' && $sendMoneyConfirm->transactionDetails) {
                            $withdrawal->update([
                                'wallet_id' => $wallet_id,
                                'withdrawal_id' => $transactionId,
                                'status' => WithdrawalStatus::Processing,
                                'sender_name' => $wallet->name,
                                'sender_iban' => $wallet->iban
                            ]);
                        }
                    }
                }
            }
        }
    }
}
