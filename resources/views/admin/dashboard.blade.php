@extends('admin.layouts.app')

@section('title', 'Dashboard — TiendynFood Admin')
@section('topbar-title', 'Dashboard')

@section('content')

@php
  $avatarColors = [
    'background:linear-gradient(135deg,#FF6B35,#E8521A)',
    'background:linear-gradient(135deg,#3B82F6,#1D4ED8)',
    'background:linear-gradient(135deg,#22C55E,#15803D)',
    'background:linear-gradient(135deg,#EF4444,#B91C1C)',
    'background:linear-gradient(135deg,#8B5CF6,#6D28D9)',
    'background:linear-gradient(135deg,#F59E0B,#B45309)',
    'background:linear-gradient(135deg,#06B6D4,#0E7490)',
    'background:linear-gradient(135deg,#EC4899,#BE185D)',
  ];
  $statusBadgeCls = [
    'pending'   => 'bg-amber-50 text-amber-700 border border-amber-200',
    'confirmed' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'preparing' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'ready'     => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'delivered' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'cancelled' => 'bg-red-50 text-red-700 border border-red-200',
  ];
  $statusDot = [
    'pending'   => '#d97706',
    'confirmed' => '#3b82f6',
    'preparing' => '#3b82f6',
    'ready'     => '#10b981',
    'delivered' => '#10b981',
    'cancelled' => '#ef4444',
  ];
  $statusLabel = [
    'pending'   => 'Pendiente',
    'confirmed' => 'Confirmado',
    'preparing' => 'Preparando',
    'ready'     => 'Listo',
    'delivered' => 'Entregado',
    'cancelled' => 'Cancelado',
  ];
@endphp

{{-- No restaurant warning --}}
@if (!$restaurant)
  <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 mb-6">
    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-base mt-0.5">⚠️</div>
    <div>
      <p class="text-sm font-semibold text-amber-800">Sin restaurante registrado</p>
      <p class="text-[13px] text-amber-700 mt-0.5">No tienes un restaurante en el sistema. Contacta al administrador para comenzar.</p>
    </div>
  </div>
@else

{{-- ══════════════════════════════════════
     STAT CARDS
══════════════════════════════════════ --}}
<div class="grid grid-cols-4 gap-4 mb-6">

  {{-- Pedidos hoy --}}
  <div class="stat-card bg-white rounded-2xl border border-slate-100 shadow-sm p-5 cursor-default">
    <div class="flex items-center justify-between mb-4">
      <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#fff3ee,#ffe4d6);">
        <svg class="w-5 h-5" fill="none" stroke="#FF6B35" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Hoy</span>
    </div>
    <p class="text-[32px] font-extrabold text-slate-800 leading-none">{{ $todayOrders }}</p>
    <p class="text-[12.5px] text-slate-400 mt-1.5 font-medium">Pedidos hoy</p>
  </div>

  {{-- Ingresos hoy --}}
  <div class="stat-card bg-white rounded-2xl border border-slate-100 shadow-sm p-5 cursor-default">
    <div class="flex items-center justify-between mb-4">
      <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
        <svg class="w-5 h-5" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Hoy</span>
    </div>
    <p class="text-[26px] font-extrabold text-slate-800 leading-none">L.&nbsp;{{ number_format($todayRevenue, 2) }}</p>
    <p class="text-[12.5px] text-slate-400 mt-1.5 font-medium">Ingresos hoy</p>
  </div>

  {{-- Clientes únicos --}}
  <div class="stat-card bg-white rounded-2xl border border-slate-100 shadow-sm p-5 cursor-default">
    <div class="flex items-center justify-between mb-4">
      <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
        <svg class="w-5 h-5" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
      <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Hoy</span>
    </div>
    <p class="text-[32px] font-extrabold text-slate-800 leading-none">{{ $todayCustomers }}</p>
    <p class="text-[12.5px] text-slate-400 mt-1.5 font-medium">Clientes únicos</p>
  </div>

  {{-- Calificación --}}
  <div class="stat-card bg-white rounded-2xl border border-slate-100 shadow-sm p-5 cursor-default">
    <div class="flex items-center justify-between mb-4">
      <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);">
        <svg class="w-5 h-5" fill="#f59e0b" stroke="#f59e0b" stroke-width="1" viewBox="0 0 24 24">
          <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
      </div>
      @php $ratingGood = $avgRating >= 4; @endphp
      <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $ratingGood ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }}">
        {{ $ratingGood ? '↑ bueno' : '↓ bajo' }}
      </span>
    </div>
    <p class="text-[32px] font-extrabold text-slate-800 leading-none">{{ $avgRating > 0 ? number_format($avgRating, 1) : '—' }}</p>
    <p class="text-[12.5px] text-slate-400 mt-1.5 font-medium">Calificación promedio</p>
  </div>

</div>

{{-- ══════════════════════════════════════
     2-COLUMN LAYOUT
══════════════════════════════════════ --}}
<div class="grid grid-cols-3 gap-6 items-start">

  {{-- LEFT — col-span-2 --}}
  <div class="col-span-2 flex flex-col gap-6">

    {{-- ── WEEKLY BAR CHART ── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <div class="flex items-start justify-between mb-5">
        <div>
          <h3 class="text-[15px] font-bold text-slate-800">Ventas esta semana</h3>
          @php $totalWeekOrders = array_sum(array_column($weeklyOrders, 'count')); @endphp
          <p class="text-[12px] text-slate-400 mt-0.5">
            {{ $totalWeekOrders }} pedido{{ $totalWeekOrders !== 1 ? 's' : '' }} esta semana
          </p>
        </div>
        <div class="text-right">
          <div class="text-[22px] font-extrabold leading-tight" style="color:#FF6B35;">
            L.&nbsp;{{ number_format($weeklyRevenue, 0) }}
          </div>
          <div class="text-[11px] text-slate-400 mt-0.5">ingresos totales</div>
        </div>
      </div>

      {{-- Chart --}}
      <div class="flex items-end gap-1.5 h-20">
        @php $todayIndex = now()->dayOfWeek === 0 ? 6 : now()->dayOfWeek - 1; @endphp
        @foreach($weeklyOrders as $i => $dayData)
          @php
            $pct    = $maxWeekly > 0 ? round(($dayData['count'] / $maxWeekly) * 100) : 0;
            $pct    = max($pct, 6);
            $isToday = ($i === $todayIndex);
          @endphp
          <div class="flex-1 flex flex-col items-center gap-1.5">
            <div class="w-full rounded-t-md chart-bar"
                 style="height:{{ $pct }}%; background:{{ $isToday ? '#FF6B35' : '#e2e8f0' }};"></div>
            <span class="text-[10px] font-medium {{ $isToday ? 'text-brand' : 'text-slate-400' }}"
                  style="{{ $isToday ? 'color:#FF6B35;' : '' }}">
              {{ $dayData['day'] }}
            </span>
          </div>
        @endforeach
      </div>
    </div>

    {{-- ── RECENT ORDERS TABLE ── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h3 class="text-[15px] font-bold text-slate-800">Pedidos recientes</h3>
          <p class="text-[12px] mt-0.5 font-medium" style="color:#FF6B35;">
            Últimos {{ $recentOrders->count() }} pedido{{ $recentOrders->count() !== 1 ? 's' : '' }}
          </p>
        </div>
        <a href="{{ route('admin.orders') }}"
           class="text-[12px] font-semibold text-slate-400 transition-colors hover:text-orange-500 flex items-center gap-1">
          Ver todos
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      @if($recentOrders->isEmpty())
        <div class="py-14 text-center">
          <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-3">📋</div>
          <p class="text-[14px] font-semibold text-slate-700">Sin pedidos aún</p>
          <p class="text-[12.5px] text-slate-400 mt-1">Los pedidos aparecerán aquí cuando los clientes ordenen.</p>
        </div>
      @else
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-100">
                <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">Cliente</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">N° Pedido</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">Ítems</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">Total</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">Estado</th>
                <th class="px-5 py-3"></th>
              </tr>
            </thead>
            <tbody>
              @foreach($recentOrders as $idx => $order)
                @php
                  $name      = $order->user->name ?? 'Cliente';
                  $parts     = explode(' ', trim($name));
                  $initials  = strtoupper(mb_substr($parts[0],0,1) . (isset($parts[1]) ? mb_substr($parts[1],0,1) : mb_substr($parts[0],1,1)));
                  $color     = $avatarColors[$idx % count($avatarColors)];
                  $badgeCls  = $statusBadgeCls[$order->status] ?? 'bg-amber-50 text-amber-700 border border-amber-200';
                  $dot       = $statusDot[$order->status] ?? '#d97706';
                  $label     = $statusLabel[$order->status] ?? ucfirst($order->status);
                  $itemCount = $order->items ? $order->items->count() : '?';
                  $orderNum  = $order->order_number ?? ('ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT));
                @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors last:border-0">
                  <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0"
                           style="{{ $color }};">{{ $initials }}</div>
                      <div>
                        <p class="text-[13px] font-semibold text-slate-700">{{ $name }}</p>
                        <p class="text-[11px] text-slate-400">#{{ $order->id }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3.5 text-[12px] text-slate-400 font-medium">{{ $orderNum }}</td>
                  <td class="px-5 py-3.5 text-[12px] text-slate-500">{{ $itemCount }} {{ $itemCount == 1 ? 'ítem' : 'ítems' }}</td>
                  <td class="px-5 py-3.5 text-[13px] font-bold" style="color:#FF6B35;">L.&nbsp;{{ number_format($order->total, 2) }}</td>
                  <td class="px-5 py-3.5">
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1 {{ $badgeCls }}">
                      <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:{{ $dot }};"></span>
                      {{ $label }}
                    </span>
                  </td>
                  <td class="px-5 py-3.5">
                    <a href="{{ route('admin.orders', ['status' => $order->status]) }}"
                       class="text-[11.5px] font-semibold px-3 py-1.5 rounded-lg transition-colors"
                       style="background:#fff3ee; color:#FF6B35;"
                       onmouseover="this.style.background='#ffe4d6';"
                       onmouseout="this.style.background='#fff3ee';">Ver</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

  </div>

  {{-- RIGHT — col-span-1 --}}
  <div class="col-span-1 flex flex-col gap-6">

    {{-- ── ACTIVE ORDER ── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h3 class="text-[15px] font-bold text-slate-800">Pedido activo</h3>
          @if($activeOrder)
            <p class="text-[12px] mt-0.5 font-medium" style="color:#FF6B35;">
              #{{ $activeOrder->order_number ?? $activeOrder->id }}
              &nbsp;·&nbsp;{{ $activeOrder->created_at->diffForHumans() }}
            </p>
          @else
            <p class="text-[12px] text-slate-400 mt-0.5">Sin pedidos pendientes</p>
          @endif
        </div>
        @if($activeOrder)
          <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            Nuevo
          </span>
        @endif
      </div>

      @if(!$activeOrder)
        <div class="py-12 text-center">
          <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-3xl mx-auto mb-3">🎉</div>
          <p class="text-[14px] font-semibold text-slate-700">¡Todo al día!</p>
          <p class="text-[12.5px] text-slate-400 mt-1">No hay pedidos pendientes ahora.</p>
        </div>
      @else

        {{-- Items --}}
        <div class="divide-y divide-slate-50 px-0">
          @foreach($activeOrder->items as $item)
            @php $mi = $item->menuItem; @endphp
            <div class="flex items-center gap-3 px-5 py-2.5">
              <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-xl flex-shrink-0 overflow-hidden">
                @if($mi && $mi->image_url)
                  <img src="{{ $mi->image_url }}" alt="{{ $mi->name }}" class="w-full h-full object-cover">
                @else
                  🍽️
                @endif
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-slate-700 truncate">{{ $mi ? $mi->name : 'Producto eliminado' }}</p>
                <p class="text-[11px] text-slate-400">L.&nbsp;{{ number_format($item->unit_price, 2) }} c/u</p>
              </div>
              <span class="text-[13px] font-bold flex-shrink-0" style="color:#FF6B35;">×{{ $item->quantity }}</span>
            </div>
          @endforeach
        </div>

        {{-- Totals --}}
        <div class="px-5 py-4 border-t border-slate-100">
          @php
            $subtotal    = $activeOrder->items->sum('subtotal');
            $deliveryFee = $activeOrder->delivery_fee ?? 0;
          @endphp
          <div class="space-y-1.5 mb-3">
            <div class="flex justify-between text-[12px] text-slate-500">
              <span>Subtotal</span>
              <span>L.&nbsp;{{ number_format($subtotal, 2) }}</span>
            </div>
            @if($deliveryFee > 0)
              <div class="flex justify-between text-[12px] text-slate-500">
                <span>Domicilio</span>
                <span>L.&nbsp;{{ number_format($deliveryFee, 2) }}</span>
              </div>
            @endif
          </div>
          <div class="flex justify-between items-center pt-3 border-t border-slate-100">
            <span class="text-[13px] font-bold text-slate-800">Total</span>
            <span class="text-[17px] font-extrabold" style="color:#FF6B35;">L.&nbsp;{{ number_format($activeOrder->total, 2) }}</span>
          </div>

          <button
            id="confirm-btn-{{ $activeOrder->id }}"
            onclick="confirmOrder({{ $activeOrder->id }}, '{{ route('admin.orders.status', $activeOrder->id) }}')"
            class="mt-4 w-full py-3 rounded-xl text-white text-[13.5px] font-bold transition-all active:scale-95"
            style="background:linear-gradient(135deg,#FF6B35,#E8521A); box-shadow:0 4px 14px rgba(255,107,53,0.3);"
            onmouseover="this.style.boxShadow='0 6px 20px rgba(255,107,53,0.45)';"
            onmouseout="this.style.boxShadow='0 4px 14px rgba(255,107,53,0.3)';">
            Confirmar pedido
          </button>
        </div>

      @endif
    </div>

    {{-- ── POPULAR DISHES ── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h3 class="text-[15px] font-bold text-slate-800">Platos populares</h3>
          <p class="text-[12px] mt-0.5 font-medium" style="color:#FF6B35;">Top ventas del período</p>
        </div>
        <a href="{{ route('admin.menu') }}"
           class="text-[12px] font-semibold text-slate-400 hover:text-orange-500 transition-colors flex items-center gap-1">
          Ver menú
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      @if($popularItems->isEmpty())
        <div class="py-10 text-center">
          <div class="text-3xl mb-2">🍽️</div>
          <p class="text-[13px] text-slate-400">Sin datos de ventas todavía.</p>
        </div>
      @else
        <div class="divide-y divide-slate-50">
          @foreach($popularItems as $pop)
            @php $mi = $pop->menuItem; @endphp
            @if($mi)
              <div class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-xl flex-shrink-0 overflow-hidden">
                  @if($mi->image_url)
                    <img src="{{ $mi->image_url }}" alt="{{ $mi->name }}" class="w-full h-full object-cover">
                  @else
                    🍽️
                  @endif
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-[13px] font-semibold text-slate-700 truncate">{{ $mi->name }}</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">{{ number_format($pop->sales_count) }} venta{{ $pop->sales_count != 1 ? 's' : '' }}</p>
                </div>
                <span class="text-[13px] font-bold flex-shrink-0" style="color:#FF6B35;">
                  L.&nbsp;{{ number_format($mi->price, 2) }}
                </span>
              </div>
            @endif
          @endforeach
        </div>
      @endif

    </div>

  </div>
</div>

@endif

@endsection

@push('scripts')
<script>
  function confirmOrder(orderId, url) {
    const btn = document.getElementById('confirm-btn-' + orderId);
    if (!btn || btn.disabled) return;
    btn.disabled    = true;
    btn.textContent = 'Confirmando...';
    btn.style.opacity = '0.8';

    fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ status: 'confirmed' }),
    })
    .then(r => r.json())
    .then(() => {
      btn.textContent = '✓ Confirmado';
      btn.style.background = 'linear-gradient(135deg,#22c55e,#15803d)';
      btn.style.boxShadow  = '0 4px 14px rgba(34,197,94,0.3)';
      btn.style.opacity    = '1';
      showToast('Pedido confirmado correctamente', 'success');
      setTimeout(() => location.reload(), 1600);
    })
    .catch(() => {
      btn.disabled    = false;
      btn.textContent = 'Confirmar pedido';
      btn.style.opacity = '1';
      showToast('Error al confirmar el pedido', 'error');
    });
  }
</script>
@endpush
