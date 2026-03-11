<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantCategory;
use Illuminate\Http\Request;

class RestaurantCategoryController extends Controller
{
    public function index()
    {
        // Solo categorías que tengan al menos un restaurante registrado
        $categories = RestaurantCategory::whereHas('restaurants')->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }
}
