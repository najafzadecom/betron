<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
            'key' => 'required|string|max:255|unique:settings,key',
            'name' => 'required|string|max:255',
            'value' => 'nullable',
            'type' => 'required|string|in:text,number,boolean,json,file,email,url,textarea,select,radio,checkbox',
            'group' => 'required|string|max:255',
        ];
    }
}
