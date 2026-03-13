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

        return view('admin.menu.index', compact('restaurant'));
    }
}
