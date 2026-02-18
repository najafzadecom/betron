<?php

namespace App\Http\Controllers;

use App\Models\ParaQrPayIn;
use App\Models\ParaQrPayOut;
use App\Payment\ParaQrPayment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParaQrController extends Controller
{
    private ParaQrPayment $paraQrPayment;

    public function __construct(
        ParaQrPayment $paraQrPayment
    ) {
        $this->paraQrPayment = $paraQrPayment;
    }

    public function index()
    {
        $paraQrPayIns = ParaQrPayIn::all();
        return response()->json($paraQrPayIns);
    }

    /**
     * Show the application dashboard.
     *
     * @return JsonResponse
     */
    public function payIn(): JsonResponse
    {
        $data = [
            'client_order_no' => Str::uuid(),
            'sender_full_name' => 'Erhan Kulcur',
            'amount' => "50.00"
        ];

        $paraQrPayIn = ParaQrPayIn::query()->create($data);

        $response = $this->paraQrPayment->payinRequest($data);

        if ($response && $response->status) {
            $paraQrPayIn->update([
                'system_order_no' => $response->data->system_order_no,
                'reveiver_account_name' => $response->data->reveiver_account_name,
                'reveiver_account_iban' => $response->data->reveiver_account_iban,
                'response' => $response,
            ]);
        } else {
            $paraQrPayIn->update([
                'message' => $response->message,
                'response' => $response
            ]);
        }

        return response()->json($response);
    }

    public function payOut(): JsonResponse
    {
        $data = [
            'client_order_no' => Str::uuid(),
            'receiver_full_name' => 'Erhan Külçür',
            'receiver_iban' => 'TR060015700000000149462945',
            'description' => 'Geri ödeme talebi',
            'amount' => 50
        ];

        $paraQrPayOut = ParaQrPayOut::query()->create($data);

        $response = $this->paraQrPayment->payoutRequest($data);

        //print_r($response);

        Log::channel('paraqr')->info(json_encode($response));

        if ($response && isset($response->status)) {
            $paraQrPayOut->update([
                'system_order_no' => $response->data->system_order_no,
                'payout_message' => $response->data->payout_message ?? '',
                'response' => $response,
            ]);
        } else {
            $paraQrPayOut->update([
                'message' => $response->message,
                'response' => $response
            ]);
        }


        return response()->json($response);
    }


    public function receive(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            Log::channel('paraqr')->info(json_encode($request));

            if ($this->paraQrPayment->checkHash($data)) {
                if ($data['direction'] == 1) {
                    ParaQrPayOut::query()
                        ->where('system_order_no', $data['system_order_no'])
                        ->where('client_order_no', $data['client_order_no'])
                        ->update([
                            'hash' => $data['hash'],
                            'reason' => $data['reason'],
                            'status' => $data['status'],
                            'direction' => $data['direction'],
                            'callback_response' => $request->all(),
                        ]);
                } elseif ($data['direction'] == 0) {
                    ParaQrPayIn::query()
                        ->where('system_order_no', $data['system_order_no'])
                        ->where('client_order_no', $data['client_order_no'])
                        ->update([
                            'hash' => $data['hash'],
                            'reason' => $data['reason'],
                            'status' => $data['status'],
                            'direction' => $data['direction'],
                            'callback_response' => $request->all(),
                        ]);
                }
            } else {
                Log::channel('paraqr')->error(json_encode($data));
            }
        } catch (Exception $exception) {
            Log::channel('paraqr')->error(json_encode($exception));
        }

        return response()->json(['success' => true, 'code' => 200]);
    }
}
