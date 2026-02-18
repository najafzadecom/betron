<?php

namespace App\Console\Commands;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use App\Payment\Paypap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ApplyWithdrawal extends Command
{
    public function __construct(Paypap $paypap)
    {
        parent::__construct();
        $this->paypap = $paypap;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:apply-withdrawal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $withdrawals = Withdrawal::query()
            ->where('status', WithdrawalStatus::Pending)
            ->whereRaw('paid_status IS FALSE')
            ->where('amount', '>=', setting('manual_limit'))
            ->get();

        if ($withdrawals->isNotEmpty()) {
            print_r($withdrawals->toArray());
            foreach ($withdrawals as $withdrawal) {
                $paypapData = [
                    'transactionId' => $withdrawal->uuid,
                    'fullName' => $withdrawal->receiver,
                    'iban' => $withdrawal->iban,
                    'currency' => 'TRY',
                    'amount' => (int)$withdrawal->amount,
                    'user' => [
                        "userId" => (string)$withdrawal->user_id,
                        "username" => str_replace(' ', '_', mb_strtolower($withdrawal->receiver)),
                        "fullName" => $withdrawal->receiver
                    ],
                ];
                Log::channel('paypap')->info(json_encode($paypapData));
                $result = $this->paypap->createBankWithdrawal($paypapData);

                Log::channel('paypap')->info(json_encode($result, JSON_PRETTY_PRINT));
                //die();
                if (isset($result['data'])) {
                    $withdrawal->payment_method = 'paypap';
                    $withdrawal->withdrawal_id = $result['data']['withdrawalId'];
                    $withdrawal->status = WithdrawalStatus::Processing;
                    $withdrawal->save();
                }


                Log::channel('paypap')->info(json_encode($result, JSON_PRETTY_PRINT));

            }
        }
    }
}
