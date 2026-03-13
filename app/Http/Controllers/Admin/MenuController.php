<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurants()
            ->with(['categories.items'])
            ->first();

        $pendingCount = $restaurant
            ? $restaurant->orders()->where('status', 'pendiente')->count()
            : 0;

        return view('admin.menu.index', compact('restaurant', 'pendingCount'));
    }

    // ── Categories ──────────────────────────────────────────────

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $restaurant = auth()->user()->restaurants()->first();
        if (!$restaurant) return back()->with('error', 'Sin restaurante.');

        $restaurant->categories()->create([
            'name'       => $request->name,
            'sort_order' => $restaurant->categories()->count(),
        ]);

        return back()->with('success', 'Categoría creada.');
    }

    public function updateCategory(Request $request, MenuCategory $category)
    {
        $this->authorizeCategory($category);
        $request->validate(['name' => 'required|string|max:100']);
        $category->update(['name' => $request->name]);
        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroyCategory(MenuCategory $category)
    {
        $this->authorizeCategory($category);

        if ($category->items()->exists()) {
            return back()->with('error', 'No se puede eliminar "' . $category->name . '" porque tiene productos. Elimina primero los productos.');
        }
        $category->delete();
        return back()->with('success', 'Categoría eliminada.');
    }

    // ── Items ────────────────────────────────────────────────────

    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id'  => 'required|exists:menu_categories,id',
            'name'         => 'required|string|max:150',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            'is_available' => 'boolean',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
            'image_url'    => 'nullable|string|max:500',
        ]);

        $restaurant = auth()->user()->restaurants()->first();
        if (!$restaurant) return back()->with('error', 'Sin restaurante.');

        $imageUrl = $request->image_url;
        if ($request->hasFile('image')) {
            $imageUrl = app(\App\Services\FirebaseStorageService::class)
                ->upload($request->file('image'), 'products');
        }

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id'   => $request->category_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->filled('stock') ? $request->stock : -1,
            'is_available'  => $request->boolean('is_available', true),
            'image_url'     => $imageUrl,
        ]);

        return back()->with('success', 'Producto creado.');
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $this->authorizeItem($item);

        $request->validate([
            'category_id'  => 'required|exists:menu_categories,id',
            'name'         => 'required|string|max:150',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            'is_available' => 'boolean',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
            'image_url'    => 'nullable|string|max:500',
        ]);

        $imageUrl = $request->image_url ?: $item->image_url;
        if ($request->hasFile('image')) {
            $imageUrl = app(\App\Services\FirebaseStorageService::class)
                ->upload($request->file('image'), 'products', $item->image_url);
        }

        $item->update([
            'category_id'  => $request->category_id,
            'name'         => $request->name,
            'description'  => $request->description,
            'price'        => $request->price,
            'stock'        => $request->filled('stock') ? $request->stock : -1,
            'is_available' => $request->boolean('is_available', true),
            'image_url'    => $imageUrl,
        ]);

        return back()->with('success', 'Producto actualizado.');
    }

    public function destroyItem(MenuItem $item)
    {
        $this->authorizeItem($item);

        if (\DB::table('order_items')->where('menu_item_id', $item->id)->exists()) {
            return back()->with('error', 'No se puede eliminar "' . $item->name . '" porque está en pedidos registrados.');
        }

        if ($item->image_url) {
            try {
                app(\App\Services\FirebaseStorageService::class)->delete($item->image_url);
            } catch (\Throwable $e) {}
        }
        $item->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    public function toggleItem(MenuItem $item)
    {
        $this->authorizeItem($item);
        $item->update(['is_available' => !$item->is_available]);
        return response()->json(['is_available' => $item->is_available]);
    }

    // ── Helpers ─────────────────────────────────────────────────

    private function authorizeCategory(MenuCategory $category)
    {
        $restaurant = auth()->user()->restaurants()->first();
        abort_if(!$restaurant || $category->restaurant_id !== $restaurant->id, 403);
    }

    private function authorizeItem(MenuItem $item)
    {
        $restaurant = auth()->user()->restaurants()->first();
        abort_if(!$restaurant || $item->restaurant_id !== $restaurant->id, 403);
    }
}
