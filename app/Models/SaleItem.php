<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'qty', 'sale_price',
        'discount_percent', 'discount_amount', 'subtotal',
        'hpp_per_unit', 'hpp_amount', 'gross_profit',
    ];

    protected $casts = [
        'qty'              => 'decimal:2',
        'sale_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'subtotal'         => 'decimal:2',
        'hpp_per_unit'     => 'decimal:4',
        'hpp_amount'       => 'decimal:2',
        'gross_profit'     => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Detail lot FIFO yang dikonsumsi untuk item ini
    public function saleItemLots(): HasMany
    {
        return $this->hasMany(SaleItemLot::class);
    }

    public function hppRecord(): HasMany
    {
        return $this->hasMany(HppRecord::class);
    }

    public function getGrossMarginPercentAttribute(): float
    {
        if ($this->subtotal <= 0) return 0;
        return round(($this->gross_profit / $this->subtotal) * 100, 2);
    }
}
