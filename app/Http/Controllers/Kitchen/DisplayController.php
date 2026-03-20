<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DisplayController extends Controller
{
    public function index()
    {
        $restaurantId = auth()->user()->restaurant_id;

        // Pedidos aprobados (preparando) del restaurante, del más antiguo al más reciente
        $orders = Order::with(['items.menuItem', 'table'])
            ->where('restaurant_id', $restaurantId)
            ->where('status', 'preparando')
            ->orderBy('updated_at', 'asc')
            ->get();

        return view('kitchen.display', compact('orders'));
    }
}
