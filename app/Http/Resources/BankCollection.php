<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BankCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Banks retrieved successfully',
            'code' => 200,
            'total' => $this->collection->count(),
            'data' => $this->collection->map(function ($bank) {
                return [
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'image' => $bank->image_url,
                    'transaction_status' => $bank->transaction_status,
                    'withdrawal_status' => $bank->withdrawal_status,
                    'status' => $bank->status,
                ];
            }),
        ];
    }
}
