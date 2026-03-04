<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon_url', 'icon_svg'];

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class, 'category_id');
    }
}
