<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantCategory;
use Illuminate\Http\Request;

class RestaurantCategoryController extends Controller
{
    public function index()
    {
        $categories = RestaurantCategory::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }
}
