<?php

namespace App\Http\Requests\GoodsReceipt;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_order_id'        => ['required', 'exists:purchase_orders,id'],
            'receipt_date'             => ['required', 'date'],
            'invoice_number'           => ['nullable', 'string', 'max:100'],
            'invoice_date'             => ['nullable', 'date'],
            'notes'                    => ['nullable', 'string', 'max:1000'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.product_id'       => ['required', 'exists:products,id'],
            'items.*.qty_received'     => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost'        => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_order_id.required'  => 'Purchase Order wajib dipilih.',
            'receipt_date.required'       => 'Tanggal terima wajib diisi.',
            'items.required'              => 'Minimal 1 item harus diisi.',
            'items.*.qty_received.required' => 'Qty diterima wajib diisi.',
            'items.*.qty_received.min'    => 'Qty diterima minimal 0.01.',
            'items.*.unit_cost.required'  => 'Harga beli wajib diisi.',
        ];
    }
}
