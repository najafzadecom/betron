<?php

namespace App\Http\Requests\Store;

use App\Enums\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class WalletRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'iban' => ['required', 'string', 'max:255', Rule::unique('wallets', 'iban')->whereNull('deleted_at')],
            'bank_id' => 'required|integer|exists:banks,id',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'maximum_amount' => 'required|numeric|min:0',
            'single_deposit_max_amount' => 'required|numeric|min:0',
            'single_deposit_min_amount' => 'required|numeric|min:0',
            'currency' => ['required', new Enum(Currency::class)],
            'status' => 'required|integer|in:0,1,2',
            'description' => 'nullable|string',
            'transaction_banks' => 'nullable|array',
            'user_ids' => 'nullable|array',
            'phone' => 'nullable|string|max:255',
            'mobile_banking_password' => 'nullable|string|max:255',
            'linked_card' => 'boolean',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240', // Max 10MB per file
            'created_by_vendor_user_id' => 'nullable|integer|exists:vendor_users,id',
            'manager_ids' => 'nullable|array',
            'manager_ids.*' => 'integer|exists:vendor_users,id'
        ];
    }
}
