<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantSchedule;
use App\Models\DeliveryZone;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    protected $storageService;

    public function __construct(\App\Services\FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index(Request $request)
    {
        $query = Restaurant::with('restaurantCategory')
            ->where('is_open', true);

        // Filtrar por categoría si se pasa
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $limit = (int) $request->get('limit', 0);
        $restaurants = $limit > 0 ? $query->limit($limit)->get() : $query->get();

        return response()->json([
            'status' => 'success',
            'data'   => $restaurants
        ]);
    }

    /**
     * Obtener la información del restaurante del usuario autenticado (POST con Token)
     */
    public function getMyRestaurantData(Request $request)
    {
        $user = $request->user();
        
        // Optimización: Usar el índice de owner_id y cargar solo lo necesario
        $restaurant = Restaurant::select('id', 'owner_id', 'category_id', 'name', 'description', 'address', 'city', 'phone', 'logo_url', 'is_open', 'service_type')
                                ->with(['restaurantCategory:id,name', 'schedules', 'paymentMethods', 'deliveryZones'])
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()->first()
            ], 422);
        }

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $logoUrl = $this->storageService->upload($request->file('logo'), 'restaurants');
        }

        $restaurant = Restaurant::create([
            'owner_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'city' => $request->city,
            'phone' => $request->phone,
            'category_id' => $request->category_id,
            'logo_url' => $logoUrl
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo_url' => 'nullable|string',
            'service_type' => 'nullable|in:local,delivery,both',
            'delivery_zones' => 'nullable|array',
            'delivery_zones.*.name' => 'required_with:delivery_zones|string|max:150',
            'delivery_zones.*.fee' => 'required_with:delivery_zones|numeric|min:0',
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

            $data = $request->only([
                'name', 'description', 'address', 'phone', 'category_id', 'logo_url',
                'service_type',
            ]);

            // Manejo de la imagen en Firebase
            if ($request->hasFile('logo')) {
                $data['logo_url'] = $this->storageService->upload(
                    $request->file('logo'), 
                    'restaurants', 
                    $restaurant->logo_url
                );
            }

            // 1. Actualizar datos básicos
            $restaurant->update($data);

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

            // 3. Actualizar Zonas de Entrega
            if ($request->has('delivery_zones')) {
                $restaurant->deliveryZones()->delete();
                foreach ($request->delivery_zones as $zone) {
                    if (!empty($zone['name'])) {
                        DeliveryZone::create([
                            'restaurant_id' => $restaurant->id,
                            'name' => $zone['name'],
                            'fee' => $zone['fee'] ?? 0,
                        ]);
                    }
                }
            }

            // 4. Actualizar Métodos de Pago
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
                'data' => $restaurant->load(['schedules', 'paymentMethods', 'deliveryZones'])
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
        $restaurant = Restaurant::with(['restaurantCategory', 'categories.items', 'schedules', 'paymentMethods', 'deliveryZones'])->find($id);

        if (!$restaurant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restaurante no encontrado'
            ], 404);
        }

        $restaurant->avg_score     = round($restaurant->ratings()->avg('score') ?? 0, 1);
        $restaurant->ratings_count = $restaurant->ratings()->count();

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

    public function stats(Request $request)
    {
        $user        = $request->user();
        $restaurant  = Restaurant::where('owner_id', $user->id)->first();

        if (!$restaurant) {
            return response()->json(['status' => 'error', 'message' => 'Restaurante no encontrado'], 404);
        }

        $today = now()->toDateString();

        $totalRevenue = \App\Models\Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'entregado')
            ->whereDate('created_at', $today)
            ->sum('total');

        $todayOrders = \App\Models\Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['preparando', 'listo', 'entregado'])
            ->whereDate('created_at', $today)
            ->count();

        $ratingData = \App\Models\Rating::where('restaurant_id', $restaurant->id)
            ->selectRaw('AVG(score) as avg_score, COUNT(*) as count')
            ->first();

        return response()->json([
            'status'        => 'success',
            'total_revenue' => (float) $totalRevenue,
            'today_orders'  => (int) $todayOrders,
            'avg_rating'    => $ratingData->avg_score ? round((float) $ratingData->avg_score, 1) : null,
            'ratings_count' => (int) $ratingData->count,
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
