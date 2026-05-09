<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:50', 'unique:units,name'],
            'symbol'    => ['required', 'string', 'max:10', 'unique:units,symbol'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Nama satuan wajib diisi.',
            'name.unique'     => 'Nama satuan sudah ada.',
            'symbol.required' => 'Simbol wajib diisi.',
            'symbol.unique'   => 'Simbol sudah digunakan.',
        ];
    }
}