<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurants()->first();

        $todayOrders     = $restaurant ? $restaurant->orders()->whereDate('created_at', today())->count() : 0;
        $todayRevenue    = $restaurant ? $restaurant->orders()->whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total') : 0;
        $todayCustomers  = $restaurant ? $restaurant->orders()->whereDate('created_at', today())->distinct('user_id')->count('user_id') : 0;
        $avgRating       = $restaurant ? (float) $restaurant->ratings()->avg('score') : 0;

        $recentOrders = $restaurant
            ? $restaurant->orders()->with(['user', 'items.menuItem'])->latest()->take(8)->get()
            : collect();

        $activeOrder = $restaurant
            ? $restaurant->orders()->where('status', 'pending')->with(['user', 'items.menuItem'])->latest()->first()
            : null;

        $pendingCount = $restaurant
            ? $restaurant->orders()->where('status', 'pending')->count()
            : 0;

        // Weekly orders (Mon–Sun of current week)
        $weeklyOrders  = [];
        $weeklyRevenue = 0;

        for ($i = 0; $i < 7; $i++) {
            $day   = now()->startOfWeek()->addDays($i);
            $count = $restaurant
                ? $restaurant->orders()->whereDate('created_at', $day)->count()
                : 0;

            $weeklyOrders[] = [
                'day'   => $day->locale('es')->isoFormat('ddd'),
                'count' => $count,
            ];

            $weeklyRevenue += $restaurant
                ? $restaurant->orders()->whereDate('created_at', $day)->where('status', '!=', 'cancelled')->sum('total')
                : 0;
        }

        $maxWeekly = max(array_column($weeklyOrders, 'count')) ?: 1;

        // Popular items (top 3 by order count)
        $popularItems = $restaurant
            ? OrderItem::whereHas('order', fn ($q) => $q->where('restaurant_id', $restaurant->id))
                ->select('menu_item_id', DB::raw('count(*) as sales_count'))
                ->groupBy('menu_item_id')
                ->orderByDesc('sales_count')
                ->take(3)
                ->with('menuItem')
                ->get()
            : collect();

        return view('admin.dashboard', compact(
            'restaurant',
            'todayOrders',
            'todayRevenue',
            'todayCustomers',
            'avgRating',
            'recentOrders',
            'activeOrder',
            'pendingCount',
            'weeklyOrders',
            'weeklyRevenue',
            'maxWeekly',
            'popularItems'
        ));
    }
}
