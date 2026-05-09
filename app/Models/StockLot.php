<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockLot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'goods_receipt_id', 'lot_number',
        'received_date', 'unit_cost', 'qty_initial',
        'qty_remaining', 'is_exhausted', 'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'unit_cost'     => 'decimal:2',
        'qty_initial'   => 'decimal:2',
        'qty_remaining' => 'decimal:2',
        'is_exhausted'  => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItemLots(): HasMany
    {
        return $this->hasMany(SaleItemLot::class);
    }

    // Scope kunci FIFO: lot tersedia, urut dari terlama
    public function scopeAvailable($query)
    {
        return $query->where('is_exhausted', false)
                     ->where('qty_remaining', '>', 0)
                     ->orderBy('received_date', 'asc')
                     ->orderBy('id', 'asc');
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Total nilai lot ini (qty_remaining × unit_cost)
    public function getCurrentValueAttribute(): float
    {
        return (float) ($this->qty_remaining * $this->unit_cost);
    }
}
