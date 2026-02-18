<?php

namespace App\Http\Requests\Update;

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
        // Get user ID from route parameter (could be 'user' or 'vendor_user')
        $userId = $this->route('user') ?? $this->route('vendor_user');

        // Build unique rule with proper exclusion
        // Only add exclusion if userId is valid and numeric (not empty string)
        $emailRule = 'required|email|max:255|unique:vendor_users,email';
        if ($userId !== '' && is_numeric($userId)) {
            $emailRule .= ',' . (int)$userId;
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];

        if (!auth()->guard('vendor')->check()) {
            $rules['vendor_id'] = 'required|exists:vendors,id';
        }

        return $rules;
    }
}
