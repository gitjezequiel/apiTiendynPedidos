<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'category_id', 'name', 'description', 
        'price', 'image_url', 'is_available', 'emoji', 'stock', 'extras'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'extras' => 'array',
        'price' => 'float',
        'stock' => 'integer',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }
}
