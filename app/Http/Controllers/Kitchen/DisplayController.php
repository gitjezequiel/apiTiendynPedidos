<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DisplayController extends Controller
{
    public function index()
    {
        $restaurantId = auth()->user()->restaurant_id;

        $orders = Order::with(['items.menuItem', 'table'])
            ->where('restaurant_id', $restaurantId)
            ->where('status', 'preparando')
            ->orderBy('updated_at', 'asc')
            ->get();

        $ordersJson = $orders->map(function ($o) {
            return [
                'id'            => $o->id,
                'order_number'  => $o->order_number,
                'customer_name' => $o->customer_name,
                'delivery_mode' => $o->delivery_mode,
                'notes'         => $o->notes,
                'updated_at'    => $o->updated_at->toIso8601String(),
                'table'         => $o->table ? ['number' => $o->table->number] : null,
                'items'         => $o->items->map(function ($i) {
                    return [
                        'quantity' => $i->quantity,
                        'name'     => $i->menuItem->name ?? $i->name ?? 'Ítem',
                        'notes'    => $i->notes ?? null,
                    ];
                }),
            ];
        });

        return view('kitchen.display', compact('orders', 'ordersJson'));
    }

    public function markListo(Request $request, Order $order)
    {
        if ($order->restaurant_id !== auth()->user()->restaurant_id) {
            abort(403);
        }

        if ($order->status !== 'preparando') {
            return response()->json(['ok' => false, 'message' => 'El pedido ya no está en preparación.'], 422);
        }

        $order->update(['status' => 'listo']);

        // Notificar al dueño y al cliente vía Firebase
        try {
            $firestore = new \App\Services\FirestoreService();

            // Al dueño del restaurante
            $restaurant = $order->restaurant()->first();
            if ($restaurant) {
                $firestore->addDocument('notifications', [
                    'user_id'    => (string) $restaurant->owner_id,
                    'type'       => 'status_update',
                    'title'      => 'Pedido listo',
                    'message'    => '✅ El pedido ' . $order->order_number . ' está listo',
                    'read'       => false,
                    'created_at' => time() * 1000,
                    'data'       => [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'status'       => 'listo',
                    ],
                ]);
            }

            // Al cliente
            if ($order->user_id) {
                $firestore->addDocument('notifications', [
                    'user_id'    => (string) $order->user_id,
                    'type'       => 'status_update',
                    'title'      => 'Actualización de pedido',
                    'message'    => '✅ Tu pedido está listo para recoger/entregar (' . $order->order_number . ')',
                    'read'       => false,
                    'created_at' => time() * 1000,
                    'data'       => [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'status'       => 'listo',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Firebase kitchen notification error: ' . $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    public function ordersJson()
    {
        $restaurantId = auth()->user()->restaurant_id;

        $orders = Order::with(['items.menuItem', 'table'])
            ->where('restaurant_id', $restaurantId)
            ->where('status', 'preparando')
            ->orderBy('updated_at', 'asc')
            ->get()
            ->map(function ($order) {
                return [
                    'id'            => $order->id,
                    'order_number'  => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'delivery_mode' => $order->delivery_mode,
                    'notes'         => $order->notes,
                    'updated_at'    => $order->updated_at->toIso8601String(),
                    'updated_ago'   => $order->updated_at->diffForHumans(null, true),
                    'table'         => $order->table ? ['number' => $order->table->number] : null,
                    'items'         => $order->items->map(fn($item) => [
                        'quantity' => $item->quantity,
                        'name'     => $item->menuItem->name ?? $item->name ?? 'Ítem',
                        'notes'    => $item->notes ?? null,
                    ]),
                ];
            });

        return response()->json(['orders' => $orders]);
    }
}
