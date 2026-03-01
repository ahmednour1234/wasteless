<?php
// app/Models/Company.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;   // ← بدلاً من Model
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Company extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'active',
        'category_id',
        'approve',
        'logo',
        'email_verify_code'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
      protected $casts = [
    'password' => 'hashed',
  ];

    /* شفّر كلمة المرور تلقائياً */
    protected function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }
        public function branches()
    {
        return $this->hasMany(Branch::class);
    }
      public function Category()
    {
        return $this->BelongsTo(Category::class);
    }
}
