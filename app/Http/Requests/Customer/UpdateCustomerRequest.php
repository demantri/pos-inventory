<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('customer')->id;

        return [
            'code'      => ['required', 'string', 'max:20', "unique:customers,code,{$id}"],
            'name'      => ['required', 'string', 'max:200'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:100'],
            'address'   => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode customer wajib diisi.',
            'code.unique'   => 'Kode customer sudah digunakan.',
            'name.required' => 'Nama customer wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
        ];
    }
}