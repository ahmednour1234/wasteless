<?php

// app/Models/Branch.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $fillable = [
        'lat',
        'lng',
        'name',
        'address',
        'phone',
        'company_id',
        'main',
                'active',    // ← add here

    ];

    /**
     * The company this branch belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
