<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuItemController extends Controller
{
    public function index($restaurant_id)
    {
        $items = MenuItem::where('restaurant_id', $restaurant_id)
            ->with('category')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $items
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'category_id' => 'nullable|exists:menu_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string|max:500',
            'is_available' => 'boolean'
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

        // Si se provee categoría, validar que sea del mismo restaurante
        if ($request->has('category_id')) {
            $category = MenuCategory::find($request->category_id);
            if ($category && $category->restaurant_id != $request->restaurant_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La categoría no pertenece a este restaurante'
                ], 400);
            }
        }

        $item = MenuItem::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Platillo creado exitosamente',
            'data' => $item
        ], 201);
    }

    public function show($id)
    {
        $item = MenuItem::with('category')->find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Platillo no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $item
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = MenuItem::find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Platillo no encontrado'
            ], 404);
        }

        $restaurant = Restaurant::find($item->restaurant_id);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        // Si se actualiza categoría, validar coherencia
        if ($request->has('category_id')) {
            $category = MenuCategory::find($request->category_id);
            if ($category && $category->restaurant_id != $item->restaurant_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La categoría no pertenece a este restaurante'
                ], 400);
            }
        }

        $item->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Platillo actualizado exitosamente',
            'data' => $item
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $item = MenuItem::find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Platillo no encontrado'
            ], 404);
        }

        $restaurant = Restaurant::find($item->restaurant_id);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Platillo eliminado exitosamente'
        ]);
    }
}
