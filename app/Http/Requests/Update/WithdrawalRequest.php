<?php

namespace App\Http\Requests\Update;

use App\Enums\Currency;
use App\Enums\PaymentProvider;
use App\Enums\WithdrawalStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class WithdrawalRequest extends FormRequest
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
            'user_id' => 'nullable|integer',
            'wallet_id' => 'nullable|integer|exists:wallets,id',
            'sender_name' => 'nullable|string|max:255',
            'sender_iban' => 'nullable|string|max:34',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'bank_id' => 'nullable|integer|exists:banks,id',
            'bank_name' => 'nullable|string|max:255',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'iban' => 'required|string|max:34',
            'amount' => 'required|numeric|min:0.01',
            'fee' => 'nullable|integer|min:0|max:100',
            'fee_amount' => 'nullable|numeric|min:0',
            'order_id' => 'nullable|string|max:255',
            'currency' => ['required', new Enum(Currency::class)],
            'status' => ['required', new Enum(WithdrawalStatus::class)],
            'payment_method' => ['nullable', new Enum(PaymentProvider::class)],
            'paid_status' => 'boolean',
            'manual' => 'boolean',
            'site_id' => 'nullable|integer|min:1'
        ];
    }
}
