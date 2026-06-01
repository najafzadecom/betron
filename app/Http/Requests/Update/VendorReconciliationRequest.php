<?php

namespace App\Http\Requests\Update;

use Illuminate\Foundation\Http\FormRequest;

class VendorReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'devir' => 'required|numeric',
            'yatirim' => 'required|numeric',
            'man_yatirim' => 'required|numeric',
            'cekim' => 'required|numeric',
            'man_cekim' => 'required|numeric',
            'y_komisyon' => 'required|numeric',
            'teslimat' => 'required|numeric',
            't_komisyon' => 'required|numeric',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
