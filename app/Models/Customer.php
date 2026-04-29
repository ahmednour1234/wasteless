<?php
// app/Models/Customer.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'img', 'password',
        'loyalty_points', 'loyalty_bonus_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'loyalty_bonus_expires_at' => 'datetime',
    ];

    /* تشفير كلمة المرور تلقائياً */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function getLoyaltyTierAttribute(): string
    {
        $points = $this->loyalty_points ?? 0;
        if ($points >= 65001) return 'platinum';
        if ($points >= 20001) return 'gold';
        return 'silver';
    }
}
