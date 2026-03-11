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
    protected $storageService;

    public function __construct(\App\Services\FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Campo para el archivo
            'image_url' => 'nullable|string|max:500',
            'is_available' => 'boolean',
            'emoji' => 'nullable|string|max:20',
            'stock' => 'nullable|integer',
            'extras' => 'nullable|array'
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

        $data = $request->all();

        // Manejo de la imagen en Firebase
        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storageService->upload($request->file('image'), 'products');
        }

        $item = MenuItem::create($data);

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

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:150',
            'price' => 'numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:menu_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $this->formatValidationErrors($validator)
            ], 422);
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

        $data = $request->all();

        // Manejo de la imagen en Firebase (actualización)
        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storageService->upload(
                $request->file('image'), 
                'products', 
                $item->image_url // Se pasa la URL vieja para borrarla
            );
        }

        $item->update($data);

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

        // Eliminar imagen de Firebase si existe
        if ($item->image_url) {
            $this->storageService->delete($item->image_url);
        }

        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Platillo eliminado exitosamente'
        ]);
    }

    protected function formatValidationErrors($validator)
    {
        $errors = $validator->errors()->all();
        return $errors[0] ?? 'Error de validación';
    }
}
