<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuCategoryController extends Controller
{
    public function index($restaurant_id)
    {
        $categories = MenuCategory::where('restaurant_id', $restaurant_id)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:100',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $this->formatValidationErrors($validator)
            ], 422);
        }

        $restaurant = Restaurant::find($request->restaurant_id);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado para este restaurante'
            ], 403);
        }

        $category = MenuCategory::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Categoría creada exitosamente',
            'data' => $category
        ], 201);
    }

    public function show($id)
    {
        $category = MenuCategory::with('items')->find($id);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = MenuCategory::find($id);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        $restaurant = Restaurant::find($category->restaurant_id);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $category->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $category = MenuCategory::find($id);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        $restaurant = Restaurant::find($category->restaurant_id);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Categoría eliminada exitosamente'
        ]);
    }
}
