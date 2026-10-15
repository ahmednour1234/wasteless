<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED      = 'fixed';

    protected $fillable = [
        'code', 'type', 'value', 'max_discount', 'min_order_amount',
        'start_date', 'end_date', 'usage_limit', 'usage_limit_per_user', 'is_active',
    ];

    protected $casts = [
        'value'            => 'float',
        'max_discount'     => 'float',
        'min_order_amount' => 'float',
        'start_date'       => 'datetime',
        'end_date'         => 'datetime',
        'is_active'        => 'boolean',
    ];

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function usages()
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
