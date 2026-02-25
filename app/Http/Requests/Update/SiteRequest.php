<?php

namespace App\Http\Requests\Update;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('sites-update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:sites,name,' . $this->route('site'),
            'url' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'description' => 'nullable|string',
            'transaction_fee' => 'required|numeric|min:0|max:100',
            'withdrawal_fee' => 'required|numeric|min:0|max:100',
            'settlement_fee' => 'required|numeric|min:0|max:100',
            'status' => 'boolean'
        ];
    }
}
