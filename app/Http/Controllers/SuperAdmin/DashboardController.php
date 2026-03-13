<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRestaurants = Restaurant::count();
        $totalOwners      = User::where('role', 'owner')->count();
        $totalCustomers   = User::where('role', 'customer')->count();
        $totalOrders      = Order::count();
        $totalRevenue     = Order::whereIn('status', ['entregado'])->sum('total');

        $recentRestaurants = Restaurant::with('owner')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentOrders = Order::with(['user', 'restaurant'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Orders per day last 7 days
        $weeklyOrders = Order::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        return view('superadmin.dashboard', compact(
            'totalRestaurants', 'totalOwners', 'totalCustomers',
            'totalOrders', 'totalRevenue',
            'recentRestaurants', 'recentOrders', 'weeklyOrders'
        ));
    }
}
