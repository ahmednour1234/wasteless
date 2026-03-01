<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
 protected $fillable = [
    'order_id',
    'bundle_id',
    'company_id',
    'branch_id',
    'category_id',
    'quantity',
    'price',
    'discount',     // تمت الإضافة هنا
    'total',
    'bundles',
];


    protected $casts = [
        'bundles' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }
}
