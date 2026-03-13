<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if (!$restaurant) {
            return view('admin.orders.index', [
                'restaurant'   => null,
                'orders'       => collect(),
                'pendingCount' => 0,
            ]);
        }

        $query = $restaurant->orders()->with(['user', 'items.menuItem', 'deliveryZone'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders       = $query->paginate(15)->withQueryString();
        $pendingCount   = $restaurant->orders()->where('status', 'pendiente')->count();
        $preparandoCount = $restaurant->orders()->where('status', 'preparando')->count();
        $listoCount      = $restaurant->orders()->where('status', 'listo')->count();

        return view('admin.orders.index', compact('restaurant', 'orders', 'pendingCount', 'preparandoCount', 'listoCount'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:pendiente,preparando,listo,entregado,cancelado,rechazado'],
        ]);

        $restaurant = auth()->user()->restaurants()->first();

        if (!$restaurant || $order->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
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
                \Log::warning('Firebase customer notification error (admin): ' . $fe->getMessage());
            }
        }

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'status'  => $order->status,
        ]);
    }
}
