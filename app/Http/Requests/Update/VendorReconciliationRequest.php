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
            'cekim_iptal' => 'required|numeric',
            'y_komisyon_oran' => 'required|numeric|min:0|max:100',
            'teslimat' => 'required|numeric',
            't_komisyon_oran' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
