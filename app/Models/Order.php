<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id', 'status', 'sub_total', 'total_discount', 'delivery', 'address', 'phone', 'name',
        'loyalty_discount', 'points_redeemed', 'has_bonus_discount',
        'promo_code_id', 'promo_code', 'promo_type', 'promo_value', 'promo_discount',
        'commission_percentage', 'commission_amount',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }
}
