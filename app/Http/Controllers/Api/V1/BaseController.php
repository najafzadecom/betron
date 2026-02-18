<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseController extends Controller
{
    public function response(
        array  $data = [],
        bool   $success = true,
        int    $code = 200,
        string $message = 'OK',
        int    $status = 200
    ): JsonResponse {
        $data = [
            'success' => $success,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($data, $status);
    }
}
