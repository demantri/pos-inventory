<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('unit')->id;

        return [
            'name'      => ['required', 'string', 'max:50', "unique:units,name,{$id}"],
            'symbol'    => ['required', 'string', 'max:10', "unique:units,symbol,{$id}"],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Nama satuan wajib diisi.',
            'name.unique'     => 'Nama satuan sudah digunakan.',
            'symbol.required' => 'Simbol wajib diisi.',
            'symbol.unique'   => 'Simbol sudah digunakan.',
        ];
    }
}