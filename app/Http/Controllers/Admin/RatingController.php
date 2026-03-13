<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if (!$restaurant) {
            return view('admin.ratings.index', [
                'restaurant'   => null,
                'ratings'      => collect(),
                'avgScore'     => 0,
                'totalRatings' => 0,
                'pendingCount' => 0,
            ]);
        }

        $pendingCount = $restaurant->orders()->where('status', 'pendiente')->count();

        $query = Rating::where('restaurant_id', $restaurant->id)
            ->with(['user', 'order'])
            ->orderByDesc('created_at');

        if ($request->filled('score')) {
            $query->where('score', $request->score);
        }

        $ratings      = $query->paginate(20)->withQueryString();
        $avgScore     = Rating::where('restaurant_id', $restaurant->id)->avg('score') ?? 0;
        $totalRatings = Rating::where('restaurant_id', $restaurant->id)->count();

        // Score distribution
        $distribution = Rating::where('restaurant_id', $restaurant->id)
            ->selectRaw('score, COUNT(*) as total')
            ->groupBy('score')
            ->pluck('total', 'score')
            ->toArray();

        return view('admin.ratings.index', compact(
            'restaurant', 'ratings', 'avgScore', 'totalRatings', 'distribution', 'pendingCount'
        ));
    }
}
