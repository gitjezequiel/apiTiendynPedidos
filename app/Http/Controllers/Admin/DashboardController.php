<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $restaurant = $user->restaurants()->first();

        if (!$restaurant) {
            return view('admin.dashboard', ['restaurant' => null]);
        }

        $totalOrders   = $restaurant->orders()->count();
        $pendingOrders = $restaurant->orders()->where('status', 'pending')->count();
        $todayOrders   = $restaurant->orders()
            ->whereDate('created_at', today())
            ->count();
        $totalRevenue  = $restaurant->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $recentOrders = $restaurant->orders()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'restaurant',
            'totalOrders',
            'pendingOrders',
            'todayOrders',
            'totalRevenue',
            'recentOrders'
        ));
    }
}
