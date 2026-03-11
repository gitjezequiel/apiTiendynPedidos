<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Follow;

class FavoriteController extends Controller
{
    /** Listar favoritos del usuario autenticado */
    public function index(Request $request)
    {
        $favorites = Follow::where('user_id', $request->user()->id)
            ->with('restaurant.restaurantCategory')
            ->get()
            ->pluck('restaurant')
            ->filter();

        return response()->json(['status' => 'success', 'data' => $favorites->values()]);
    }

    /** Toggle: agregar o quitar de favoritos */
    public function toggle(Request $request, $restaurantId)
    {
        $userId = $request->user()->id;

        $existing = Follow::where('user_id', $userId)->where('restaurant_id', $restaurantId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'success', 'favorited' => false]);
        }

        Follow::create(['user_id' => $userId, 'restaurant_id' => $restaurantId]);
        return response()->json(['status' => 'success', 'favorited' => true]);
    }

    /** Verificar si un restaurante es favorito */
    public function check(Request $request, $restaurantId)
    {
        $favorited = Follow::where('user_id', $request->user()->id)
            ->where('restaurant_id', $restaurantId)
            ->exists();

        return response()->json(['status' => 'success', 'favorited' => $favorited]);
    }
}
