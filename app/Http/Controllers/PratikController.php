<?php

namespace App\Http\Controllers;

use App\Payment\PratikPayment;
use Illuminate\Http\JsonResponse;

class PratikController extends Controller
{
    private PratikPayment $pratikPayment;

    public function __construct(
        PratikPayment $pratikPayment
    ) {
        $this->pratikPayment = $pratikPayment;
    }

    public function index()
    {
        $passCode = $this->pratikPayment->myConfirmKey();
        //exit();
        $walletId = '98AA8C32-C6CE-429A-8F8D-90561854752D';

        $receiver = 'Burak Önen';
        $iban = 'TR690015700000000145719745';
        $amount = 50000;
        $extTransactionId = '4080063213';

        //$transactionId = 'F45C5096-150A-4B36-A0F6-6447D6EDEC57';
        $passCode = base64_encode($passCode);

        $transaction = $this->pratikPayment->sendMoneyToBank($walletId, $receiver, $iban, $amount, $extTransactionId);
        print_r($transaction);
        $transactionId = $transaction->transactionDetails[0]->transactionId;
        print_r($this->pratikPayment->sendMoneyConfirm($walletId, $amount, $transactionId, $passCode));
        exit();
        print_r($this->pratikPayment->mySecretKey());
        print_r($this->pratikPayment->moneyTransactionHistory());
        print_r($this->pratikPayment->checkBusinessWallet());
        print_r($this->pratikPayment->myAccountBasic());
        print_r($this->pratikPayment->myIbanSave());
        print_r($this->pratikPayment->myBalance());
    }
}
