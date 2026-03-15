<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $table = 'restaurant_tables';

    protected $fillable = ['restaurant_id', 'name'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
