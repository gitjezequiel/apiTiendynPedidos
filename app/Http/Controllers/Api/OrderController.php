<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Crear un nuevo pedido (Solo Clientes)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'delivery_address' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
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

            // 3. Crear el pedido
            $order = Order::create([
                'user_id' => $user->id,
                'restaurant_id' => $request->restaurant_id,
                'order_number' => $orderNumber,
                'status' => 'pendiente',
                'total' => $total,
                'delivery_address' => $request->delivery_address,
                'notes' => $request->notes
            ]);

            // 4. Crear los detalles del pedido
            foreach ($itemsToCreate as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido realizado exitosamente',
                'order' => $order->load('items.menuItem')
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
        $user = $request->user();

        if ($user->role === 'customer') {
            $orders = Order::with('restaurant', 'items.menuItem')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        } else {
            // Un dueño puede tener varios restaurantes
            $restaurantIds = Restaurant::where('owner_id', $user->id)->pluck('id');
            $orders = Order::with('user', 'items.menuItem')
                ->whereIn('restaurant_id', $restaurantIds)
                ->latest()
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'orders' => $orders
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
