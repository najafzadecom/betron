<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\PaymentProvider;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'user_id' => 'required|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'iban' => 'required|string|max:34',
            'bank_id' => 'required|integer|exists:banks,id',
            'amount' => 'required|numeric|min:0.01',
            'order_id' => 'nullable|string|max:255',
            'site_id' => 'required|integer|min:1',
            'withdrawal_fee' => 'required|numeric|min:0.01',
            'vendor_id' => 'nullable|exists:vendors,id',
        ];
    }

    public function prepareForValidation(): void
    {
        // Set default payment_method to manual if not provided
        if (!$this->has('payment_method')) {
            $data['payment_method'] = PaymentProvider::Manual->value;
        }

        $this->merge($data);
    }
}
