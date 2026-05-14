<?php

namespace App\Http\Requests\Update;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BlacklistRequest extends FormRequest
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
            'type' => 'required|in:user_id,ip_address',
            'user_id' => 'required_if:type,user_id|nullable|string|max:255',
            'site_id' => 'required|integer|exists:sites,id',
            'ip_address' => 'required_if:type,ip_address|nullable|ip',
            'reason' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $userId = $this->input('user_id');
        if ($userId !== null && $userId !== '') {
            $this->merge([
                'user_id' => is_string($userId) ? trim($userId) : (string) $userId,
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
