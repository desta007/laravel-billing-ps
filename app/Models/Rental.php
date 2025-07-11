<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    protected $fillable = [
        'customer_id',
        'console_id',
        'start_time',
        'end_time',
        'duration_hours',
        'total_cost',
        'is_paid',
    ];

    public function console()
    {
        return $this->belongsTo(Console::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
