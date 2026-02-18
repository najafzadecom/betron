<?php

namespace App\Http\Requests\Update;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VendorRoleRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:roles,name,' . $this->route('role'),
            'guard_name' => 'required|string|in:vendor',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id,guard_name,vendor',
            'color' => 'nullable|string|max:50',
            'status' => 'required|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'guard_name' => 'vendor',
        ]);
    }
}
