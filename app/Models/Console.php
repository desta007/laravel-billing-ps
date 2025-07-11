<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    protected $fillable = [
        'name',
        'rate_per_hour',
        'status',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
