<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id', 'qty_ordered',
        'qty_received', 'unit_cost', 'discount_percent', 'subtotal', 'notes',
    ];

    protected $casts = [
        'qty_ordered'      => 'decimal:2',
        'qty_received'     => 'decimal:2',
        'unit_cost'        => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    // Sisa qty yang belum diterima
    public function getRemainingQtyAttribute(): float
    {
        return (float) ($this->qty_ordered - $this->qty_received);
    }

    public function isFullyReceived(): bool
    {
        return $this->qty_received >= $this->qty_ordered;
    }
}
