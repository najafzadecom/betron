<?php

namespace App\Http\Requests\Store;

use App\Enums\Currency;
use App\Enums\TransactionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'user_id' => 'required|integer',
            'order_id' => 'required|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'currency' => ['required', new Enum(Currency::class)],
            'wallet_id' => 'nullable|integer|exists:wallets,id',
            'bank_id' => 'nullable|integer|exists:banks,id',
            'client_ip' => 'required|ip',
            'site_id' => 'required|integer|min:1',
            'status' => ['required', new Enum(TransactionStatus::class)],
            'paid_status' => 'boolean',
        ];
    }
}
