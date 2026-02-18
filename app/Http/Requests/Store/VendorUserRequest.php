<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VendorUserRequest extends FormRequest
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
            'vendor_id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:vendor_users,email',
            'password' => 'required|string|min:6|confirmed',
            'status' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];
    }

    public function prepareForValidation(): void
    {
        if (auth()->guard('vendor')->check()) {
            $this->merge([
                'vendor_id' => auth()->guard('vendor')->id(),
            ]);
        }
    }
}
