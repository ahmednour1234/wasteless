<?php
// app/Models/Review.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_id',
        'customer_id',
        'bundle_data',
        'rating',
        'comment',
        'active',
    ];

    protected $casts = [
        'bundle_data' => 'array',
    ];

    /* relations */
    public function bundle()   { return $this->belongsTo(Bundle::class,'bundle_id'); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
