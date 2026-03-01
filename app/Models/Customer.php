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
    ];

    protected $hidden = [
        'password', 'remember_token',
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
}
