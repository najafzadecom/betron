<?php

namespace App\Http\Requests\Store;

use App\Enums\PaymentProvider;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProviderRequest extends FormRequest
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
        $rules = [
            'name' => 'required',
            'code' => 'required|unique:providers,code,' . ($this->provider?->id ?? 'NULL'),
            'type' => 'required|in:' . implode(',', array_column(PaymentProvider::cases(), 'value')),
            'credentials' => 'array',
            'status' => 'boolean',
        ];

        $type = $this->input('type');

        if ($type && file_exists(config_path("payment/{$type}.php"))) {
            $requiredCredentials = config("payment.{$type}.required_credentials", []);

            foreach ($requiredCredentials as $key) {
                $rules["credentials.$key"] = 'required|string';
            }
        }

        return $rules;
    }
}
