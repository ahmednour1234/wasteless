<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    const PAYMENT_TYPE_WHISH_MONEY = 'whish_money';
    const PAYMENT_TYPE_OMT_PAY = 'omt_pay';
    const PAYMENT_TYPE_BANK = 'bank';

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'external_id',
        'payment_type',
        'amount',
        'currency',
        'status',
        'collect_url',
        'collect_status',
        'payer_phone_number',
        'invoice',
        'success_callback_url',
        'failure_callback_url',
        'success_redirect_url',
        'failure_redirect_url',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
