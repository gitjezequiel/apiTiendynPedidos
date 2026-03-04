<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'category_id', 'name', 'description', 'address', 'city', 'phone', 
        'logo_url', 'cover_image_url', 'is_open', 'rating', 'total_ratings'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function restaurantCategory()
    {
        return $this->belongsTo(RestaurantCategory::class, 'category_id');
    }

    public function categories()
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function followers()
    {
        return $this->hasMany(Follow::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
