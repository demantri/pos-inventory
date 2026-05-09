<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'customer_id', 'sale_number', 'sale_date',
        'payment_method', 'subtotal', 'discount_amount',
        'tax_percent', 'tax_amount', 'total_amount',
        'paid_amount', 'change_amount', 'total_hpp',
        'gross_profit', 'status', 'notes',
    ];

    protected $casts = [
        'sale_date'       => 'datetime',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent'     => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
        'total_hpp'       => 'decimal:2',
        'gross_profit'    => 'decimal:2',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_VOIDED    = 'voided';

    const PAYMENT_CASH        = 'cash';
    const PAYMENT_QRIS        = 'qris';
    const PAYMENT_TRANSFER    = 'transfer';
    const PAYMENT_DEBIT_CARD  = 'debit_card';
    const PAYMENT_CREDIT_CARD = 'credit_card';
    const PAYMENT_MIXED       = 'mixed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function hppRecords(): HasMany
    {
        return $this->hasMany(HppRecord::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('sale_date', [$from, $to]);
    }

    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('sale_date', $date);
    }

    public function getGrossMarginPercentAttribute(): float
    {
        if ($this->total_amount <= 0) return 0;
        return round(($this->gross_profit / $this->total_amount) * 100, 2);
    }
}
