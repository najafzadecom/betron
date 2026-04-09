<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CashevoController extends Controller
{
    /**
     * Cashevo POST callback (application/json).
     */
    public function callback(Request $request): JsonResponse
    {
        Log::channel('cashevo')->info('Cashevo callback', [
            'payload' => $request->all(),
        ]);

        return response()->json(['received' => true]);
    }
}
