<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    protected $fillable = ['user_id', 'bundle_id'];

    /**
     * user_id يخزّن customers.id وليس users.id.
     */
    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }
}
