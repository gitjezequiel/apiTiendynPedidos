<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cocina — TiendynFood</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    @keyframes slide-in {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .order-card { animation: slide-in 0.35s ease both; }

    @keyframes ping-slow {
      0%, 100% { transform: scale(1); opacity: 1; }
      50%       { transform: scale(1.15); opacity: 0.7; }
    }
    .badge-ping { animation: ping-slow 1.8s ease-in-out infinite; }

    /* Scrollbar minimal */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
  </style>
</head>
<body class="min-h-screen" style="background:#0f172a;">

{{-- ══ TOPBAR ══ --}}
<header class="flex items-center justify-between px-6 py-3 border-b" style="background:#0f172a; border-color:rgba(255,255,255,0.06);">
  <div class="flex items-center gap-3">
    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
         style="background:linear-gradient(135deg,#FF6B35,#E8521A); box-shadow:0 4px 14px rgba(255,107,53,0.4);">
      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
    </div>
    <div>
      <div class="text-white font-extrabold text-base leading-tight">Cocina</div>
      <div class="text-slate-400 text-xs">{{ auth()->user()->name }}</div>
    </div>
  </div>

  <div class="flex items-center gap-4">
    {{-- Contador de pedidos activos --}}
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl" style="background:rgba(255,107,53,0.12);">
      <span id="order-count"
            class="badge-ping text-lg font-black"
            style="color:#FF6B35;">{{ $orders->count() }}</span>
      <span class="text-slate-400 text-xs font-medium">en preparación</span>
    </div>

    {{-- Reloj --}}
    <div class="text-right">
      <div id="clock" class="text-white font-bold text-lg tabular-nums"></div>
      <div id="date-display" class="text-slate-500 text-xs capitalize"></div>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('kitchen.logout') }}">
      @csrf
      <button type="submit"
              class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-slate-400 text-xs font-medium transition-colors hover:text-red-400 hover:bg-red-500/10">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Salir
      </button>
    </form>
  </div>
</header>

{{-- ══ CONTENIDO ══ --}}
<main class="p-6" id="orders-container">

  @if($orders->isEmpty())
    {{-- Estado vacío --}}
    <div id="empty-state" class="flex flex-col items-center justify-center py-32">
      <div class="w-20 h-20 rounded-3xl flex items-center justify-center mb-6"
           style="background:rgba(255,107,53,0.08);">
        <svg class="w-10 h-10" style="color:#FF6B35;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <p class="text-slate-300 font-bold text-xl">Sin pedidos en preparación</p>
      <p class="text-slate-500 text-sm mt-2">Cuando el dueño apruebe un pedido, aparecerá aquí.</p>
      <p class="text-slate-600 text-xs mt-6">Actualización automática cada 20 segundos</p>
    </div>
  @else
    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">

      @foreach($orders as $index => $order)
      <div class="order-card rounded-2xl overflow-hidden border"
           style="background:#1e293b; border-color:rgba(255,255,255,0.07); animation-delay: {{ $index * 0.05 }}s;">

        {{-- Header del pedido --}}
        <div class="px-4 py-3 flex items-center justify-between border-b" style="border-color:rgba(255,255,255,0.06);">
          <div class="flex items-center gap-2.5">
            {{-- Número de orden --}}
            <div class="px-2.5 py-1 rounded-lg text-white text-xs font-black tracking-wide"
                 style="background:linear-gradient(135deg,#FF6B35,#E8521A);">
              {{ $order->order_number }}
            </div>
            {{-- Mesa --}}
            @if($order->table)
              <div class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold"
                   style="background:rgba(99,102,241,0.15); color:#a5b4fc;">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18"/>
                </svg>
                Mesa {{ $order->table->number }}
              </div>
            @else
              <span class="text-xs text-slate-500 font-medium">
                {{ $order->delivery_mode === 'delivery' ? '🛵 Delivery' : '🥡 Para llevar' }}
              </span>
            @endif
          </div>

          {{-- Tiempo desde que fue aprobado --}}
          <div class="flex items-center gap-1.5">
            <div class="w-2 h-2 rounded-full bg-amber-400" style="animation: ping-slow 1.8s ease-in-out infinite;"></div>
            <span class="text-amber-400 text-xs font-semibold" title="{{ $order->updated_at->format('H:i:s') }}">
              {{ $order->updated_at->diffForHumans(null, true) }}
            </span>
          </div>
        </div>

        {{-- Cliente --}}
        @if($order->customer_name)
        <div class="px-4 pt-3 pb-0">
          <p class="text-slate-400 text-xs font-medium">
            <span class="text-slate-500">Cliente:</span>
            <span class="text-slate-300 font-semibold ml-1">{{ $order->customer_name }}</span>
          </p>
        </div>
        @endif

        {{-- Items --}}
        <div class="px-4 py-3 space-y-2.5">
          @foreach($order->items as $item)
          <div class="flex items-start gap-3">
            {{-- Cantidad --}}
            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white font-black text-sm flex-shrink-0"
                 style="background:rgba(255,107,53,0.15); color:#FF6B35;">
              {{ $item->quantity }}
            </div>
            {{-- Nombre --}}
            <div class="flex-1 min-w-0 pt-0.5">
              <p class="text-white text-sm font-semibold leading-snug">
                {{ $item->menuItem->name ?? $item->name ?? 'Ítem' }}
              </p>
              @if($item->notes)
                <p class="text-amber-400 text-xs mt-0.5 font-medium">⚠ {{ $item->notes }}</p>
              @endif
            </div>
          </div>
          @endforeach
        </div>

        {{-- Notas del pedido --}}
        @if($order->notes)
        <div class="mx-4 mb-3 px-3 py-2 rounded-xl border border-amber-500/20" style="background:rgba(245,158,11,0.07);">
          <p class="text-amber-400 text-xs font-medium">
            <span class="font-bold">Nota:</span> {{ $order->notes }}
          </p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="px-4 pb-3">
          <div class="text-xs text-slate-600 text-right">
            Pedido #{{ $index + 1 }} en cola
          </div>
        </div>

      </div>
      @endforeach

    </div>

    <p class="text-center text-slate-600 text-xs mt-8">Actualización automática cada 20 segundos</p>
  @endif

</main>

<script>
  // Reloj
  function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString('es-HN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    document.getElementById('date-display').textContent = now.toLocaleDateString('es-HN', { weekday: 'long', day: 'numeric', month: 'short' });
  }
  updateClock();
  setInterval(updateClock, 1000);

  // Auto-refresh cada 20 segundos
  setTimeout(() => location.reload(), 20000);
</script>

</body>
</html>
