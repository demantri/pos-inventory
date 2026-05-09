<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HppRecord extends Model
{
    protected $fillable = [
        'sale_id', 'sale_item_id', 'product_id', 'period_date',
        'total_qty_sold', 'sale_revenue', 'total_hpp',
        'avg_hpp_per_unit', 'gross_profit', 'gross_margin_percent',
    ];

    protected $casts = [
        'period_date'          => 'date',
        'total_qty_sold'       => 'decimal:2',
        'sale_revenue'         => 'decimal:2',
        'total_hpp'            => 'decimal:2',
        'avg_hpp_per_unit'     => 'decimal:4',
        'gross_profit'         => 'decimal:2',
        'gross_margin_percent' => 'decimal:4',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeInPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('period_date', [$from, $to]);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
}
