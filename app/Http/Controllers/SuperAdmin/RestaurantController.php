<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::with('owner')
            ->withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('status', 'entregado')], 'total')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'like', "%$s%");
        }

        $restaurants = $query->paginate(20)->withQueryString();

        return view('superadmin.restaurants', compact('restaurants'));
    }

    public function toggleActive(Restaurant $restaurant)
    {
        $restaurant->update(['is_open' => !$restaurant->is_open]);
        return back()->with('success', 'Estado actualizado.');
    }
}
