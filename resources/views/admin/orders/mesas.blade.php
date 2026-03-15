@extends('admin.layouts.app')

@section('title', 'Mesas — TiendynFood Admin')
@section('topbar-title', 'Estado de Mesas')

@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Estado de Mesas</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">Vista en tiempo real de las mesas de hoy</p>
  </div>
  <div class="flex items-center gap-3">
    @if($restaurant)
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-emerald-200 bg-emerald-50">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="text-[12px] font-semibold text-emerald-700">{{ $freeCount }} libre{{ $freeCount != 1 ? 's' : '' }}</span>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-orange-200 bg-orange-50">
          <span class="w-2 h-2 rounded-full bg-orange-500"></span>
          <span class="text-[12px] font-semibold text-orange-700">{{ $busyCount }} ocupada{{ $busyCount != 1 ? 's' : '' }}</span>
        </div>
      </div>
      <a href="{{ route('admin.orders.create') }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-[13px] font-bold shadow-sm transition-all"
         style="background:#FF6B35;"
         onmouseover="this.style.background='#E8521A';"
         onmouseout="this.style.background='#FF6B35';">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Tomar Pedido
      </a>
    @endif
    <button onclick="location.reload()"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-[13px] font-semibold border border-slate-200 bg-white text-slate-600 transition-all"
            onmouseover="this.style.borderColor='#FF6B35'; this.style.color='#FF6B35';"
            onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
      Actualizar
    </button>
  </div>
</div>

@if (!$restaurant)
  <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-base">⚠️</div>
    <div>
      <p class="text-sm font-semibold text-amber-800">Sin restaurante registrado</p>
      <p class="text-[13px] text-amber-700 mt-0.5">No tienes un restaurante en el sistema.</p>
    </div>
  </div>
@elseif($tables->isEmpty())
  <div class="bg-white rounded-2xl border border-dashed border-slate-200 py-20 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-4">🪑</div>
    <p class="text-[15px] font-bold text-slate-700 mb-1">Sin mesas configuradas</p>
    <p class="text-[13px] text-slate-400 mb-4">Agrega mesas desde la pantalla de tomar pedido.</p>
    <a href="{{ route('admin.orders.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-[13px] font-bold"
       style="background:#FF6B35;">
      Ir a Tomar Pedido
    </a>
  </div>
@else

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    @foreach($tables as $table)
      @php
        $order = $activeOrders[$table->id] ?? null;
        $busy  = !is_null($order);
      @endphp

      <div class="bg-white rounded-2xl border-2 shadow-sm flex flex-col overflow-hidden transition-all"
           style="{{ $busy
             ? 'border-color:#fed7aa; box-shadow:0 4px 16px rgba(255,107,53,0.12);'
             : 'border-color:#d1fae5;' }}">

        {{-- Card header --}}
        <div class="px-4 pt-4 pb-3 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                 style="{{ $busy ? 'background:#fff5f0;' : 'background:#f0fdf4;' }}">
              🪑
            </div>
            <div>
              <p class="text-[14px] font-bold text-slate-800">{{ $table->name }}</p>
              <span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-full"
                    style="{{ $busy
                      ? 'background:#fff5f0; color:#FF6B35; border:1px solid #fed7aa;'
                      : 'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;' }}">
                {{ $busy ? 'Ocupada' : 'Libre' }}
              </span>
            </div>
          </div>
        </div>

        @if($busy)
          {{-- Customer name --}}
          <div class="px-4 pb-2">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0"
                   style="background:linear-gradient(135deg,#FF6B35,#E8521A);">
                {{ strtoupper(mb_substr($order->customer_name ?? 'C', 0, 1)) }}
              </div>
              <div class="min-w-0">
                <p class="text-[13px] font-bold text-slate-800 truncate">
                  {{ $order->customer_name ?? 'Sin nombre' }}
                </p>
                <p class="text-[11px] text-slate-400 font-mono">{{ $order->order_number }}</p>
              </div>
            </div>
          </div>

          {{-- Status badge --}}
          <div class="px-4 pb-2">
            @php
              $statusStyle = match($order->status) {
                'pendiente'  => 'background:#fffbeb; color:#b45309; border:1px solid #fde68a;',
                'preparando' => 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;',
                'listo'      => 'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;',
                default      => 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;',
              };
              $statusLabel = match($order->status) {
                'pendiente'  => '⏳ Pendiente',
                'preparando' => '🍳 Preparando',
                'listo'      => '✅ Listo',
                default      => ucfirst($order->status),
              };
            @endphp
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full inline-block" style="{{ $statusStyle }}">
              {{ $statusLabel }}
            </span>
          </div>

          {{-- Items summary --}}
          @if($order->items->isNotEmpty())
            <div class="mx-3 mb-3 px-3 py-2 rounded-xl bg-slate-50 flex flex-col gap-1">
              @foreach($order->items->take(3) as $item)
                <p class="text-[11.5px] text-slate-600 truncate">
                  <span class="font-bold text-slate-700">{{ $item->quantity }}×</span>
                  {{ $item->menuItem?->name ?? 'Producto eliminado' }}
                </p>
              @endforeach
              @if($order->items->count() > 3)
                <p class="text-[11px] text-slate-400 italic">+{{ $order->items->count() - 3 }} más…</p>
              @endif
            </div>
          @endif

          {{-- Total + time --}}
          <div class="px-4 pb-3 flex items-center justify-between">
            <span class="text-[14px] font-extrabold" style="color:#FF6B35;">L. {{ number_format($order->total, 2) }}</span>
            <span class="text-[11px] text-slate-400">{{ $order->created_at->format('H:i') }}</span>
          </div>

          {{-- Action --}}
          <a href="{{ route('admin.orders', ['status' => $order->status]) }}"
             class="mx-3 mb-3 flex items-center justify-center gap-1.5 py-2 rounded-xl text-[12px] font-bold transition-all border"
             style="border-color:#FF6B35; color:#FF6B35; background:#fff5f0;"
             onmouseover="this.style.background='#FF6B35'; this.style.color='#fff';"
             onmouseout="this.style.background='#fff5f0'; this.style.color='#FF6B35';">
            Ver pedido
          </a>

        @else
          {{-- Free table placeholder --}}
          <div class="flex-1 flex flex-col items-center justify-center py-6 px-4 text-center">
            <p class="text-[12px] text-slate-400 mb-3">Mesa disponible</p>
            <a href="{{ route('admin.orders.create') }}"
               class="text-[11.5px] font-bold px-3 py-1.5 rounded-lg transition-all"
               style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;"
               onmouseover="this.style.background='#dcfce7';"
               onmouseout="this.style.background='#f0fdf4';">
              + Asignar pedido
            </a>
          </div>
        @endif

      </div>
    @endforeach
  </div>

@endif

@endsection
