<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('product')->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id'     => ['required', 'exists:units,id'],
            'code'        => ['required', 'string', 'max:50', "unique:products,code,{$id}"],
            'barcode'     => ['nullable', 'string', 'max:100', "unique:products,barcode,{$id}"],
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sale_price'  => ['required', 'numeric', 'min:0'],
            'min_stock'   => ['required', 'numeric', 'min:0'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'unit_id.required'     => 'Satuan wajib dipilih.',
            'code.required'        => 'Kode produk wajib diisi.',
            'code.unique'          => 'Kode produk sudah digunakan.',
            'barcode.unique'       => 'Barcode sudah digunakan.',
            'name.required'        => 'Nama produk wajib diisi.',
            'sale_price.required'  => 'Harga jual wajib diisi.',
            'min_stock.required'   => 'Stok minimum wajib diisi.',
            'image.image'          => 'File harus berupa gambar.',
            'image.max'            => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}