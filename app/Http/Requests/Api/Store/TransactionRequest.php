<?php

namespace App\Http\Requests\Api\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'bank_id' => 'required|integer|exists:banks,id',
            'client_ip' => 'required|ip',
            'order_id' => 'required|integer',
            'site_id' => 'required|integer|exists:sites,id',
            'site_name' => 'required|string|max:255',
            'transaction_fee' => 'required|numeric|min:0.01',
            'user_id' => 'required|integer',
        ];
    }
}
