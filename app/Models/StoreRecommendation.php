<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'store_name',
        'location_hint',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
