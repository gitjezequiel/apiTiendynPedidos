<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function create()
    {
        $restaurant = auth()->user()->restaurants()
            ->with(['categories.items' => fn($q) => $q->where('is_available', true)])
            ->first();

        $pendingCount = $restaurant
            ? $restaurant->orders()->where('status', 'pendiente')->count()
            : 0;

        // IDs de mesas ocupadas hoy (con pedido activo)
        $busyTableIds = $restaurant
            ? $restaurant->orders()
                ->whereDate('created_at', today())
                ->whereIn('status', ['pendiente', 'preparando', 'listo'])
                ->whereNotNull('table_id')
                ->pluck('table_id')
                ->unique()
                ->values()
            : collect();

        $tables = $restaurant
            ? $restaurant->tables()->orderBy('name')->get()
            : collect();

        return view('admin.orders.create', compact('restaurant', 'pendingCount', 'tables', 'busyTableIds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_id'      => 'nullable|integer|exists:restaurant_tables,id',
            'items'         => 'required|array|min:1',
            'items.*.id'    => 'required|integer|exists:menu_items,id',
            'items.*.qty'   => 'required|integer|min:1',
            'notes'         => 'nullable|string|max:255',
        ]);

        $restaurant = auth()->user()->restaurants()->first();
        if (!$restaurant) {
            return response()->json(['message' => 'Sin restaurante registrado.'], 422);
        }

        // Verificar que la mesa no esté ocupada hoy
        if ($request->filled('table_id')) {
            $tableBusy = $restaurant->orders()
                ->whereDate('created_at', today())
                ->whereIn('status', ['pendiente', 'preparando', 'listo'])
                ->where('table_id', $request->table_id)
                ->exists();

            if ($tableBusy) {
                return response()->json(['message' => 'Esa mesa ya tiene un pedido activo hoy.'], 422);
            }

            // Verificar que la mesa pertenece al restaurante
            abort_unless($restaurant->tables()->where('id', $request->table_id)->exists(), 403);
        }

        DB::beginTransaction();
        try {
            $total = 0;
            $lines = [];

            foreach ($request->items as $item) {
                $menuItem = $restaurant->items()->findOrFail($item['id']);
                $subtotal = $menuItem->price * $item['qty'];
                $total   += $subtotal;
                $lines[]  = [
                    'menu_item_id' => $menuItem->id,
                    'quantity'     => $item['qty'],
                    'unit_price'   => $menuItem->price,
                    'subtotal'     => $subtotal,
                ];
            }

            $order = Order::create([
                'user_id'       => auth()->id(),
                'restaurant_id' => $restaurant->id,
                'table_id'      => $request->table_id ?: null,
                'order_number'  => 'PED-' . strtoupper(substr(uniqid(), -5)),
                'status'        => 'pendiente',
                'source'        => 'local',
                'total'         => $total,
                'delivery_mode' => 'pickup',
                'delivery_fee'  => 0,
                'notes'         => $request->notes,
            ]);

            foreach ($lines as $line) {
                $order->items()->create($line);
            }

            DB::commit();

            return response()->json([
                'message'      => 'Pedido creado correctamente.',
                'order_number' => $order->order_number,
                'redirect'     => route('admin.orders'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el pedido: ' . $e->getMessage()], 500);
        }
    }

    // ── Table management ──────────────────────────────────────────

    public function storeTable(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50']);
        $restaurant = auth()->user()->restaurants()->first();
        abort_unless($restaurant, 422);

        $table = $restaurant->tables()->create(['name' => $request->name]);
        return response()->json(['id' => $table->id, 'name' => $table->name]);
    }

    public function destroyTable(RestaurantTable $table)
    {
        $restaurant = auth()->user()->restaurants()->first();
        abort_unless($restaurant && $table->restaurant_id === $restaurant->id, 403);

        $table->delete();
        return response()->json(['ok' => true]);
    }

    public function index(Request $request)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if (!$restaurant) {
            return view('admin.orders.index', [
                'restaurant'   => null,
                'orders'       => collect(),
                'pendingCount' => 0,
            ]);
        }

        $query = $restaurant->orders()->with(['user', 'items.menuItem', 'deliveryZone', 'table'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders       = $query->paginate(15)->withQueryString();
        $pendingCount   = $restaurant->orders()->where('status', 'pendiente')->count();
        $preparandoCount = $restaurant->orders()->where('status', 'preparando')->count();
        $listoCount      = $restaurant->orders()->where('status', 'listo')->count();

        return view('admin.orders.index', compact('restaurant', 'orders', 'pendingCount', 'preparandoCount', 'listoCount'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:pendiente,preparando,listo,entregado,cancelado,rechazado'],
        ]);

        $restaurant = auth()->user()->restaurants()->first();

        if (!$restaurant || $order->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $order->update(['status' => $request->status]);

        // Notificar al cliente vía Firestore
        $statusMessages = [
            'preparando' => '🍳 Tu pedido está siendo preparado',
            'listo'      => '✅ Tu pedido está listo para recoger/entregar',
            'entregado'  => '🎉 Tu pedido ha sido entregado',
            'rechazado'  => '❌ Tu pedido fue rechazado',
            'cancelado'  => '❌ Tu pedido fue cancelado',
        ];

        if (isset($statusMessages[$request->status])) {
            try {
                $firestore = new \App\Services\FirestoreService();
                $firestore->addDocument('notifications', [
                    'user_id'    => (string) $order->user_id,
                    'type'       => 'status_update',
                    'title'      => 'Actualización de pedido',
                    'message'    => $statusMessages[$request->status] . ' (' . $order->order_number . ')',
                    'read'       => false,
                    'created_at' => time() * 1000,
                    'data'       => [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'status'       => $request->status,
                    ],
                ]);
            } catch (\Exception $fe) {
                \Log::warning('Firebase customer notification error (admin): ' . $fe->getMessage());
            }
        }

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'status'  => $order->status,
        ]);
    }
}
