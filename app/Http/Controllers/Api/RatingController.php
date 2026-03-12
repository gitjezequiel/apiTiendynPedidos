<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    // Listar reseñas de un restaurante
    public function index($restaurantId)
    {
        $ratings = Rating::with('user:id,name,profile_image')
            ->where('restaurant_id', $restaurantId)
            ->orderByDesc('created_at')
            ->get();

        $avg   = $ratings->avg('score');
        $count = $ratings->count();

        return response()->json([
            'status' => 'success',
            'avg_score' => $avg ? round($avg, 1) : null,
            'count'     => $count,
            'data'      => $ratings,
        ]);
    }

    // Crear una reseña (solo si el pedido está entregado y no fue calificado aún)
    public function store(Request $request, $restaurantId)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'score'    => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $user  = $request->user();
        $order = Order::where('id', $request->order_id)
                      ->where('user_id', $user->id)
                      ->where('restaurant_id', $restaurantId)
                      ->where('status', 'entregado')
                      ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pedido no válido para calificar.'], 403);
        }

        // Verificar que no haya sido calificado ya
        $exists = Rating::where('user_id', $user->id)->where('order_id', $request->order_id)->exists();
        if ($exists) {
            return response()->json(['status' => 'error', 'message' => 'Ya calificaste este pedido.'], 409);
        }

        $rating = Rating::create([
            'user_id'       => $user->id,
            'restaurant_id' => $restaurantId,
            'order_id'      => $request->order_id,
            'score'         => $request->score,
            'comment'       => $request->comment,
        ]);

        // Notificar al dueño del restaurante
        try {
            $restaurant = Restaurant::find($restaurantId);
            if ($restaurant) {
                $stars = str_repeat('⭐', $request->score);
                (new FirestoreService())->addDocument('notifications', [
                    'user_id'    => (string) $restaurant->owner_id,
                    'type'       => 'new_review',
                    'title'      => 'Nueva reseña recibida',
                    'message'    => "{$stars} {$user->name} calificó tu restaurante" . ($request->comment ? ": \"{$request->comment}\"" : '.'),
                    'read'       => false,
                    'created_at' => time() * 1000,
                    'data'       => [
                        'restaurant_id' => $restaurant->id,
                        'score'         => $request->score,
                        'reviewer_name' => $user->name,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Review notification error: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success', 'data' => $rating], 201);
    }
}
