<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if (!$restaurant) {
            return view('admin.customers.index', [
                'restaurant'   => null,
                'customers'    => collect(),
                'pendingCount' => 0,
            ]);
        }

        $pendingCount = $restaurant->orders()->where('status', 'pendiente')->count();

        // Customers who have ordered from this restaurant
        $query = User::whereHas('orders', fn($q) => $q->where('restaurant_id', $restaurant->id))
            ->withCount(['orders as total_orders' => fn($q) => $q->where('restaurant_id', $restaurant->id)])
            ->withSum(['orders as total_spent' => fn($q) => $q->where('restaurant_id', $restaurant->id)], 'total')
            ->withMax(['orders as last_order_at' => fn($q) => $q->where('restaurant_id', $restaurant->id)], 'created_at')
            ->orderByDesc('last_order_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('restaurant', 'customers', 'pendingCount'));
    }
}
