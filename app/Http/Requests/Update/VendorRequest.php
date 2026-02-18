<?php

namespace App\Http\Requests\Update;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VendorRequest extends FormRequest
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
            'email' => 'required|email|max:255|unique:vendors,email,' . $this->route('vendor'),
            'password' => 'nullable|string|min:6|confirmed',
            'deposit_amount' => 'nullable|numeric|min:0',
            'transaction_fee' => 'nullable|numeric|min:0',
            'withdrawal_fee' => 'nullable|numeric|min:0',
            'settlement_fee' => 'nullable|numeric|min:0',
            'status' => 'boolean',
            'deposit_enabled' => 'boolean',
            'withdrawal_enabled' => 'boolean',
            'minimum_withdrawal_amount' => 'nullable|numeric|min:0',
            'maximum_withdrawal_amount' => 'nullable|numeric|min:0',
        ];
    }
}
