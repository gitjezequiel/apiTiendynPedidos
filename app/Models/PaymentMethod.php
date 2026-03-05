<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon'];

    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_payment_methods')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }
}
