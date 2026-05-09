<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'stock_lot_id', 'reference_id', 'reference_type',
        'movement_type', 'qty', 'unit_cost', 'total_cost',
        'running_stock', 'notes', 'user_id', 'movement_date',
    ];

    protected $casts = [
        'qty'           => 'decimal:2',
        'unit_cost'     => 'decimal:2',
        'total_cost'    => 'decimal:2',
        'running_stock' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    const TYPE_STOCK_IN       = 'stock_in';
    const TYPE_STOCK_OUT      = 'stock_out';
    const TYPE_ADJUSTMENT_IN  = 'adjustment_in';
    const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    const TYPE_RETURN_IN      = 'return_in';
    const TYPE_RETURN_OUT     = 'return_out';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockLot(): BelongsTo
    {
        return $this->belongsTo(StockLot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic: bisa merujuk ke GoodsReceipt, Sale, atau Adjustment
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeInPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('movement_date', [$from, $to]);
    }

    public function isIncoming(): bool
    {
        return in_array($this->movement_type, [
            self::TYPE_STOCK_IN,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_RETURN_IN,
        ]);
    }
}
