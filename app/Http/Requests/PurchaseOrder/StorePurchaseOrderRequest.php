<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id'        => ['required', 'exists:suppliers,id'],
            'po_date'            => ['required', 'date'],
            'expected_date'      => ['nullable', 'date', 'after_or_equal:po_date'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty_ordered' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost'  => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required'          => 'Supplier wajib dipilih.',
            'supplier_id.exists'            => 'Supplier tidak valid.',
            'po_date.required'              => 'Tanggal PO wajib diisi.',
            'expected_date.after_or_equal'  => 'Estimasi tiba tidak boleh sebelum tanggal PO.',
            'items.required'                => 'Minimal 1 item produk harus ditambahkan.',
            'items.*.product_id.required'   => 'Produk wajib dipilih.',
            'items.*.qty_ordered.required'  => 'Qty wajib diisi.',
            'items.*.qty_ordered.min'       => 'Qty minimal 0.01.',
            'items.*.unit_cost.required'    => 'Harga beli wajib diisi.',
        ];
    }
}
