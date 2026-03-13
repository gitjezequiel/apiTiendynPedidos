<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurants()
            ->with(['categories.items'])
            ->first();

        $pendingCount = $restaurant
            ? $restaurant->orders()->where('status', 'pending')->count()
            : 0;

        return view('admin.menu.index', compact('restaurant', 'pendingCount'));
    }
}
