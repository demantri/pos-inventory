<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'sale_id', 'order_id', 'snap_token', 'gross_amount',
        'status', 'payment_type', 'midtrans_transaction_id',
        'payment_detail', 'paid_at',
    ];

    protected $casts = [
        'gross_amount'   => 'decimal:2',
        'payment_detail' => 'array',
        'paid_at'        => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    const STATUS_CANCEL  = 'cancel';
    const STATUS_EXPIRE  = 'expire';

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
