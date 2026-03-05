<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'day', 'is_active', 'opening_time', 'closing_time'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
