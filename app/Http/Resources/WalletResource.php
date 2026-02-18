<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Account retrieved successfully',
            'code' => 200,
            'data' => [
                'id' => $this->id,
                'name' => $this->name,
                'iban' => $this->iban
            ]
        ];
    }
}
