<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantSchedule;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * Obtener la información del restaurante del usuario autenticado (POST con Token)
     */
    public function getMyRestaurantData(Request $request)
    {
        $user = $request->user();
        
        // Optimización: Usar el índice de owner_id y cargar solo lo necesario
        $restaurant = Restaurant::select('id', 'owner_id', 'category_id', 'name', 'description', 'address', 'phone', 'logo_url', 'is_open')
                                ->with(['restaurantCategory:id,name', 'schedules', 'paymentMethods'])
                                ->where('owner_id', $user->id)
                                ->first();

        if (!$restaurant) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes un restaurante registrado.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $restaurant
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
                'errors' => $validator->errors()->first()
            ], 422);
        }

        $restaurant = Restaurant::create([
            'owner_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'city' => $request->city,
            'phone' => $request->phone,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Restaurante creado exitosamente',
            'data' => $restaurant->load('restaurantCategory')
        ], 201);
    }

    /**
     * Actualizar el perfil completo del restaurante del usuario autenticado
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $restaurant = Restaurant::where('owner_id', $user->id)->first();

        // Si no existe, lo creamos de forma silenciosa para que la app no falle
        if (!$restaurant) {
            $restaurant = Restaurant::create([
                'owner_id' => $user->id,
                'name' => $request->name ?? 'Mi Restaurante',
                'category_id' => $request->category_id ?? 1, // Categoría por defecto
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:150',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:restaurant_categories,id',
            'logo_url' => 'nullable|string',
            // Validar horarios
            'schedules' => 'nullable|array',
            'schedules.*.day' => 'required|string',
            'schedules.*.is_active' => 'required|boolean',
            'schedules.*.from' => 'nullable|string|max:5',
            'schedules.*.to' => 'nullable|string|max:5',
            // Validar métodos de pago
            'payment_methods' => 'nullable|array',
            'payment_methods.*.id' => 'required|exists:payment_methods,id',
            'payment_methods.*.active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Actualizar datos básicos
            $restaurant->update($request->only([
                'name', 'description', 'address', 'phone', 'category_id', 'logo_url'
            ]));

            // 2. Actualizar Horarios
            if ($request->has('schedules')) {
                foreach ($request->schedules as $sched) {
                    RestaurantSchedule::updateOrCreate(
                        ['restaurant_id' => $restaurant->id, 'day' => $sched['day']],
                        [
                            'is_active' => $sched['is_active'],
                            'opening_time' => $sched['from'] ?? null,
                            'closing_time' => $sched['to'] ?? null,
                        ]
                    );
                }
            }

            // 3. Actualizar Métodos de Pago
            if ($request->has('payment_methods')) {
                $syncData = [];
                foreach ($request->payment_methods as $method) {
                    if ($method['active']) {
                        $syncData[$method['id']] = ['is_active' => true];
                    }
                }
                $restaurant->paymentMethods()->sync($syncData);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Perfil actualizado exitosamente',
                'data' => $restaurant->load(['schedules', 'paymentMethods'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $restaurant = Restaurant::with(['restaurantCategory', 'categories.items', 'schedules', 'paymentMethods'])->find($id);
        
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
                'message' => 'No autorizado'
            ], 403);
        }

        $restaurant->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Actualizado correctamente',
            'data' => $restaurant
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) return response()->json(['status' => 'error', 'message' => 'No encontrado'], 404);
        if ($restaurant->owner_id !== $request->user()->id) return response()->json(['status' => 'error', 'message' => 'No autorizado'], 403);

        $restaurant->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Eliminado exitosamente'
        ]);
    }
}
