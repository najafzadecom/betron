<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BankRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'priority' => 'required|integer|min:0|max:255',
            'status' => 'boolean',
            'transaction_status' => 'boolean',
            'withdrawal_status' => 'boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('Bank Name'),
            'image' => __('Bank Logo'),
            'priority' => __('Priority'),
            'status' => __('Status'),
            'transaction_status' => __('Transaction Status'),
            'withdrawal_status' => __('Withdrawal Status'),
        ];
    }
}
