<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\FirestoreService;

class OrderController extends Controller
{
    /**
     * Crear un nuevo pedido (Solo Clientes)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id'    => 'required|exists:restaurants,id',
            'delivery_mode'    => 'required|in:pickup,delivery',
            'delivery_zone_id' => 'nullable|exists:delivery_zones,id',
            'delivery_address' => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:500',
            'items'            => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()->first()
            ], 422);
        }

        $user = $request->user();

        // Validar que el usuario sea un cliente
        if ($user->role !== 'customer') {
            return response()->json([
                'status' => 'error',
                'message' => 'Solo los clientes pueden realizar pedidos.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $total = 0;
            $itemsToCreate = [];

            // 1. Calcular total y preparar items
            foreach ($request->items as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                
                // Validar que el producto pertenezca al restaurante
                if ($menuItem->restaurant_id != $request->restaurant_id) {
                    throw new \Exception("El producto '{$menuItem->name}' no pertenece a este restaurante.");
                }

                $subtotal = $menuItem->price * $itemData['quantity'];
                $total += $subtotal;

                $itemsToCreate[] = [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->price,
                    'subtotal' => $subtotal
                ];
            }

            // 2. Generar número de pedido único (PED-XXXXX)
            $lastOrder = Order::latest()->first();
            $nextNumber = $lastOrder ? (int)str_replace('PED-', '', $lastOrder->order_number) + 1 : 1;
            $orderNumber = 'PED-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            // 3. Calcular costo de envío
            $deliveryFee = 0;
            if ($request->delivery_mode === 'delivery' && $request->delivery_zone_id) {
                $zone = DeliveryZone::find($request->delivery_zone_id);
                if ($zone && $zone->restaurant_id == $request->restaurant_id) {
                    $deliveryFee = $zone->fee;
                }
            }

            // 4. Crear el pedido
            $order = Order::create([
                'user_id'          => $user->id,
                'restaurant_id'    => $request->restaurant_id,
                'order_number'     => $orderNumber,
                'status'           => 'pendiente',
                'total'            => $total + $deliveryFee,
                'delivery_address' => $request->delivery_address,
                'notes'            => $request->notes,
                'delivery_mode'    => $request->delivery_mode,
                'delivery_zone_id' => $request->delivery_zone_id,
                'delivery_fee'     => $deliveryFee,
            ]);

            // 5. Crear los detalles del pedido
            foreach ($itemsToCreate as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            // Cargar relaciones necesarias para el evento y la respuesta
            $order->load(['items.menuItem', 'user']);

            // Notificar al dueño via Firebase Firestore
            $restaurant = Restaurant::find($request->restaurant_id);
            try {
                $firestore = new FirestoreService();
                $firestore->addDocument('notifications', [
                    'user_id'      => (string) $restaurant->owner_id,
                    'type'         => 'new_order',
                    'title'        => '¡Nuevo Pedido!',
                    'message'      => 'Has recibido el pedido ' . $order->order_number . ' de ' . ($order->user->name ?? 'Cliente'),
                    'read'         => false,
                    'created_at'   => time() * 1000,
                    'data'         => [
                        'order_id'      => $order->id,
                        'order_number'  => $order->order_number,
                        'total'         => $order->total,
                        'status'        => $order->status,
                        'customer_name' => $order->user->name ?? 'Cliente',
                        'items_count'   => $order->items->count(),
                    ],
                ]);
            } catch (\Exception $fe) {
                \Log::warning('Firebase notification error: ' . $fe->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido realizado exitosamente',
                'order' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo procesar el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar pedidos (Clientes ven los suyos, Dueños los de su restaurante)
     */
    public function index(Request $request)
    {
        $user    = $request->user();
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);

        if ($user->role === 'customer') {
            $paginator = Order::with('restaurant', 'items.menuItem')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);
        } else {
            $restaurantIds = Restaurant::where('owner_id', $user->id)->pluck('id');
            $query = Order::with('user', 'items.menuItem')
                ->whereIn('restaurant_id', $restaurantIds)
                ->latest();

            // Filtrar por status si se pide (ej: pending = pendiente,preparando,listo)
            if ($request->get('filter') === 'pending') {
                $query->where('status', 'pendiente');
            }

            $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        }

        return response()->json([
            'status'   => 'success',
            'orders'   => $paginator->items(),
            'has_more' => $paginator->hasMorePages(),
            'total'    => $paginator->total(),
            'page'     => $page,
        ]);
    }

    /**
     * Cambiar estado del pedido (Solo Dueños de Restaurante)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pendiente,preparando,listo,rechazado,entregado,cancelado'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Estado inválido',
                'errors' => $validator->errors()->first()
            ], 422);
        }

        $order = Order::findOrFail($id);
        $user = $request->user();

        // Validar que el usuario sea el dueño del restaurante
        $restaurant = Restaurant::find($order->restaurant_id);
        if ($restaurant->owner_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para modificar este pedido.'
            ], 403);
        }

        $order->update(['status' => $request->status]);

        // Notificar al cliente vía Firestore
        $statusMessages = [
            'preparando' => '🍳 Tu pedido está siendo preparado',
            'listo'      => '✅ Tu pedido está listo para recoger/entregar',
            'entregado'  => '🎉 Tu pedido ha sido entregado',
            'rechazado'  => '❌ Tu pedido fue rechazado',
            'cancelado'  => '❌ Tu pedido fue cancelado',
        ];
        if (isset($statusMessages[$request->status])) {
            try {
                $firestore = new \App\Services\FirestoreService();
                $firestore->addDocument('notifications', [
                    'user_id'    => (string) $order->user_id,
                    'type'       => 'status_update',
                    'title'      => 'Actualización de pedido',
                    'message'    => $statusMessages[$request->status] . ' (' . $order->order_number . ')',
                    'read'       => false,
                    'created_at' => time() * 1000,
                    'data'       => [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'status'       => $request->status,
                    ],
                ]);
            } catch (\Exception $fe) {
                \Log::warning('Firebase customer notification error: ' . $fe->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Estado del pedido actualizado a: ' . $request->status,
            'order' => $order
        ]);
    }

    /**
     * Ver detalle de un pedido
     */
    public function show($id)
    {
        $order = Order::with(['restaurant', 'user', 'items.menuItem'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'order' => $order
        ]);
    }
}
