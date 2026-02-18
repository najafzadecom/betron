<?php

namespace App\Console\Commands;

use App\Models\PratikWallet;
use App\Models\Wallet;
use App\Payment\PratikPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPratikWallets extends Command
{
    protected $signature = 'app:sync-pratik-wallets';
    protected $description = 'Sync wallet data from PratikPayment API';

    private PratikPayment $pratikPayment;

    public function __construct(PratikPayment $pratikPayment)
    {
        parent::__construct();
        $this->pratikPayment = $pratikPayment;
    }

    public function handle(): void
    {
        $response = $this->pratikPayment->myBalance();

        if (!$response || !isset($response->Success) || !$response->Success || $response->ResponseCode != '0000') {
            Log::warning('Pratik wallet sync failed', ['response' => $response]);
            $this->error('Pratik wallet sync failed.');
            return;
        }

        foreach ($response->walletInfo ?? [] as $walletData) {
            $this->syncSingleWallet($walletData);
        }

        $this->info('Pratik wallets synced successfully.');
    }

    private function syncSingleWallet(object $walletData): void
    {
        if ($walletData) {
            $walletModel = Wallet::query()->where('iban', $walletData->iban)->first();

            if ($walletModel) {
                $walletModel->update([
                    'total_amount' => ($walletData->totalBalance / 100),
                    'blocked_amount' => ($walletData->unavailableBalance / 100),
                    'last_sync_date' => now(),
                ]);

                PratikWallet::query()->updateOrCreate(
                    ['walletId' => $walletData->walletId],
                    [
                        'wallet_id' => $walletModel->id,
                        'totalBalance' => $walletData->totalBalance,
                        'unavailableBalance' => $walletData->unavailableBalance,
                        'dailyIncomingLimit' => $walletData->dailyIncomingLimit,
                        'dailyOutgoingLimit' => $walletData->dailyOutgoingLimit,
                        'iban' => $walletData->iban,
                        'bankName' => $walletData->bankName,
                        'currencyCode' => $walletData->currencyCode,
                    ]
                );
            }
        }
    }
}
