<?php

// app/Models/Bundle.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'image',
        'description',
        'company_id',
        'category_id',
        'branch_id',
        'price',
        'price_after_discount',
        'stock',
        'active',
        'opening_time',
        'ended_time',
    ];

    /** scope for active bundles */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
     public function branch()
  {
    return $this->belongsTo(Branch::class);
  } public function company()
  {
    return $this->belongsTo(Company::class,'company_id');
  }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
