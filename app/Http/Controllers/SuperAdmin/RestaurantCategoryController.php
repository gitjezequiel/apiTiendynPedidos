<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantCategory;
use Illuminate\Http\Request;

class RestaurantCategoryController extends Controller
{
    public function index()
    {
        $categories = RestaurantCategory::withCount('restaurants')->orderBy('name')->get();
        return view('superadmin.restaurant-categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        RestaurantCategory::create(['name' => $request->name, 'icon_svg' => $request->icon_svg]);
        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, RestaurantCategory $restaurantCategory)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $restaurantCategory->update(['name' => $request->name, 'icon_svg' => $request->icon_svg]);
        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(RestaurantCategory $restaurantCategory)
    {
        if ($restaurantCategory->restaurants()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay restaurantes usando esta categoría.');
        }
        $restaurantCategory->delete();
        return back()->with('success', 'Categoría eliminada.');
    }
}
