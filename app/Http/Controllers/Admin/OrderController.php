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

        $query = $restaurant->orders()->with(['user', 'items'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders       = $query->paginate(15)->withQueryString();
        $pendingCount = $restaurant->orders()->where('status', 'pending')->count();

        return view('admin.orders.index', compact('restaurant', 'orders', 'pendingCount'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,preparing,ready,delivered,cancelled'],
        ]);

        $restaurant = auth()->user()->restaurants()->first();

        if (!$restaurant || $order->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'status'  => $order->status,
        ]);
    }
}
