<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id'     => ['required', 'exists:units,id'],
            'code'        => ['required', 'string', 'max:50', 'unique:products,code'],
            'barcode'     => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
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
            'category_id.exists'   => 'Kategori tidak valid.',
            'unit_id.required'     => 'Satuan wajib dipilih.',
            'unit_id.exists'       => 'Satuan tidak valid.',
            'code.required'        => 'Kode produk wajib diisi.',
            'code.unique'          => 'Kode produk sudah digunakan.',
            'barcode.unique'       => 'Barcode sudah digunakan.',
            'name.required'        => 'Nama produk wajib diisi.',
            'sale_price.required'  => 'Harga jual wajib diisi.',
            'sale_price.min'       => 'Harga jual tidak boleh negatif.',
            'min_stock.required'   => 'Stok minimum wajib diisi.',
            'image.image'          => 'File harus berupa gambar.',
            'image.max'            => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}