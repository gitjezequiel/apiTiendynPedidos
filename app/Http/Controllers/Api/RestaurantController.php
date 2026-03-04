<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Restaurant::with('restaurantCategory')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'category_id' => 'required|exists:restaurant_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $this->formatValidationErrors($validator)
            ], 422);
        }

        $restaurant = Restaurant::create(array_merge(
            $request->all(),
            ['owner_id' => $request->user()->id]
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Restaurante creado exitosamente',
            'data' => $restaurant->load('restaurantCategory')
        ], 201);
    }

    public function show($id)
    {
        $restaurant = Restaurant::with(['restaurantCategory', 'categories.items'])->find($id);
        
        if (!$restaurant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restaurante no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $restaurant
        ]);
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restaurante no encontrado'
            ], 404);
        }

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado para editar este restaurante'
            ], 403);
        }

        $restaurant->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Restaurante actualizado exitosamente',
            'data' => $restaurant
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restaurante no encontrado'
            ], 404);
        }

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado para eliminar este restaurante'
            ], 403);
        }

        $restaurant->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Restaurante eliminado exitosamente'
        ]);
    }
}
