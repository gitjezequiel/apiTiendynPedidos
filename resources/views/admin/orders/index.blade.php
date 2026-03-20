@extends('admin.layouts.app')

@section('title', 'Pedidos — TiendynFood Admin')
@section('topbar-title', 'Pedidos')

@section('content')

@php
  $statusTabs = [
    ''           => 'Todos',
    'pendiente'  => 'Pendientes',
    'preparando' => 'Preparando',
    'listo'      => 'Listos',
    'entregado'  => 'Entregados',
    'cancelado'  => 'Cancelados',
    'rechazado'  => 'Rechazados',
  ];
  $statusBadgeCls = [
    'pendiente'  => 'bg-amber-50 text-amber-700 border border-amber-200',
    'preparando' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'listo'      => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'entregado'  => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'cancelado'  => 'bg-red-50 text-red-700 border border-red-200',
    'rechazado'  => 'bg-red-50 text-red-700 border border-red-200',
  ];
  $statusDot = [
    'pendiente'  => '#d97706',
    'preparando' => '#3b82f6',
    'listo'      => '#10b981',
    'entregado'  => '#10b981',
    'cancelado'  => '#ef4444',
    'rechazado'  => '#ef4444',
  ];
  $statusLabel = [
    'pendiente'  => 'Pendiente',
    'preparando' => 'Preparando',
    'listo'      => 'Listo',
    'entregado'  => 'Entregado',
    'cancelado'  => 'Cancelado',
    'rechazado'  => 'Rechazado',
  ];
  $currentStatus = request('status', '');
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
@endphp

{{-- ── PAGE HEADER ── --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Pedidos</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">
      Gestiona todos los pedidos de {{ $restaurant ? $restaurant->name : 'tu restaurante' }}
    </p>
  </div>
  <div class="flex items-center gap-3">
    @if(isset($orders))
      <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-white border border-slate-200 shadow-sm">
        <span class="text-[12px] text-slate-500 font-medium">Total:</span>
        <span class="text-[15px] font-bold text-slate-800">{{ $orders->total() }}</span>
        <span class="text-[11px] text-slate-400">pedidos</span>
      </div>
    @endif
    @if($restaurant)
      <a href="{{ route('admin.orders.create') }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-[13px] font-bold shadow-sm transition-all"
         style="background:#FF6B35;"
         onmouseover="this.style.background='#E8521A'; this.style.boxShadow='0 4px 14px rgba(255,107,53,0.4)';"
         onmouseout="this.style.background='#FF6B35'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Tomar Pedido
      </a>
    @endif
  </div>
</div>

@if (!$restaurant)
  <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-base mt-0.5">⚠️</div>
    <div>
      <p class="text-sm font-semibold text-amber-800">Sin restaurante registrado</p>
      <p class="text-[13px] text-amber-700 mt-0.5">No tienes un restaurante en el sistema.</p>
    </div>
  </div>
@else

{{-- ── FILTER PILLS ── --}}
<div class="flex flex-wrap gap-2 mb-5">
  @foreach($statusTabs as $value => $label)
    @php $isActive = ($currentStatus === $value); @endphp
    <a href="{{ route('admin.orders', $value !== '' ? ['status' => $value] : []) }}"
       class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[12.5px] font-semibold border transition-all"
       style="{{ $isActive
           ? 'background:#FF6B35; color:#fff; border-color:#FF6B35; box-shadow:0 2px 10px rgba(255,107,53,0.3);'
           : 'background:#fff; color:#64748b; border-color:#e2e8f0;' }}"
       @if(!$isActive)
         onmouseover="this.style.borderColor='#FF6B35'; this.style.color='#FF6B35';"
         onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';"
       @endif>
      {{ $label }}
      @php
        $tabCount = match($value) {
          'pendiente'  => $pendingCount ?? 0,
          'preparando' => $preparandoCount ?? 0,
          'listo'      => $listoCount ?? 0,
          default      => 0,
        };
      @endphp
      @if($tabCount > 0)
        <span class="inline-flex items-center justify-center text-[9px] font-bold rounded-full px-1.5 py-0.5"
              style="{{ $isActive ? 'background:rgba(255,255,255,0.25); color:#fff;' : 'background:#FF6B35; color:#fff;' }}">
          {{ $tabCount }}
        </span>
      @endif
    </a>
  @endforeach
</div>

{{-- ── ORDERS CARD ── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

  @if($orders->isEmpty())
    <div class="py-16 text-center">
      <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-4">📋</div>
      <p class="text-[15px] font-bold text-slate-700 mb-1">Sin pedidos</p>
      <p class="text-[13px] text-slate-400">
        @if($currentStatus)
          No hay pedidos con estado "{{ $statusLabel[$currentStatus] ?? $currentStatus }}".
        @else
          Los pedidos aparecerán aquí cuando los clientes ordenen.
        @endif
      </p>
    </div>
  @else
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-100">
            <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3.5">Cliente</th>
            <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3.5">N° Pedido</th>
            <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3.5">Resumen</th>
            <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3.5">Total</th>
            <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3.5">Estado</th>
            <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3.5">Fecha</th>
            <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3.5">Acciones</th>
          </tr>
        </thead>
        <tbody id="orders-tbody">
          @foreach($orders as $idx => $order)
            @php
              $name      = $order->user->name ?? 'Cliente';
              $parts     = explode(' ', trim($name));
              $initials  = strtoupper(mb_substr($parts[0],0,1) . (isset($parts[1]) ? mb_substr($parts[1],0,1) : mb_substr($parts[0],1,1)));
              $color     = $avatarColors[($order->id) % count($avatarColors)];
              $badgeCls  = $statusBadgeCls[$order->status] ?? 'bg-amber-50 text-amber-700 border border-amber-200';
              $dot       = $statusDot[$order->status] ?? '#d97706';
              $label     = $statusLabel[$order->status] ?? ucfirst($order->status);
              $orderNum  = $order->order_number ?? ('ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT));
            @endphp
            <tr id="order-row-{{ $order->id }}" class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors last:border-0">

              {{-- Customer --}}
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0"
                       style="{{ $color }};">{{ $initials }}</div>
                  <div>
                    <p class="text-[13px] font-semibold text-slate-700">{{ $name }}</p>
                    @if($order->source === 'local' && $order->customer_name)
                      <p class="text-[11px] font-medium" style="color:#FF6B35;">{{ $order->customer_name }}</p>
                    @else
                      <p class="text-[11px] text-slate-400">#{{ $order->id }}</p>
                    @endif
                  </div>
                </div>
              </td>

              {{-- Order number --}}
              <td class="px-5 py-3.5">
                <span class="text-[12px] text-slate-500 font-medium font-mono">{{ $orderNum }}</span>
              </td>

              {{-- Items summary --}}
              <td class="px-5 py-3.5">
                <div class="flex flex-col gap-1.5">
                  @if($order->source === 'local')
                    <span class="inline-flex items-center gap-1 text-[10.5px] font-bold px-2 py-0.5 rounded-full w-fit"
                          style="background:#fff5f0; color:#FF6B35; border:1px solid #fed7c3;">
                      🪑 Local
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 text-[10.5px] font-bold px-2 py-0.5 rounded-full w-fit"
                          style="background:#eff6ff; color:#3b82f6; border:1px solid #bfdbfe;">
                      📱 App
                    </span>
                  @endif
                  @if($order->items && $order->items->count())
                    <span class="text-[12px] text-slate-400">
                      {{ $order->items->count() }} ítem{{ $order->items->count() != 1 ? 's' : '' }}
                    </span>
                  @endif
                </div>
              </td>

              {{-- Total --}}
              <td class="px-5 py-3.5">
                <span class="text-[13.5px] font-bold" style="color:#FF6B35;">L.&nbsp;{{ number_format($order->total, 2) }}</span>
              </td>

              {{-- Status badge --}}
              <td class="px-5 py-3.5">
                <span id="badge-{{ $order->id }}"
                      class="text-[11px] font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1 {{ $badgeCls }}">
                  <span class="badge-dot w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:{{ $dot }};"></span>
                  <span class="badge-text">{{ $label }}</span>
                </span>
              </td>

              {{-- Date --}}
              <td class="px-5 py-3.5">
                <span class="text-[12px] text-slate-500 font-medium">{{ $order->created_at->format('d/m/Y') }}</span>
                <br>
                <span class="text-[11px] text-slate-300">{{ $order->created_at->format('H:i') }}</span>
              </td>

              {{-- Actions --}}
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-2">
                  <select
                    id="select-{{ $order->id }}"
                    class="text-[12px] border border-slate-200 rounded-lg px-2.5 py-1.5 bg-white text-slate-600 outline-none transition-colors cursor-pointer"
                    style="min-width:130px;"
                    onfocus="this.style.borderColor='#FF6B35';"
                    onblur="this.style.borderColor='#e2e8f0';">
                    <option value="pendiente"  {{ $order->status === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                    <option value="preparando" {{ $order->status === 'preparando' ? 'selected' : '' }}>Preparando</option>
                    <option value="listo"      {{ $order->status === 'listo'      ? 'selected' : '' }}>Listo</option>
                    <option value="entregado"  {{ $order->status === 'entregado'  ? 'selected' : '' }}>Entregado</option>
                    <option value="cancelado"  {{ $order->status === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                    <option value="rechazado"  {{ $order->status === 'rechazado'  ? 'selected' : '' }}>Rechazado</option>
                  </select>
                  <button
                    onclick="updateOrderStatus({{ $order->id }}, '{{ route('admin.orders.status', $order->id) }}')"
                    class="text-[11.5px] font-bold text-white px-3 py-1.5 rounded-lg transition-all cursor-pointer"
                    style="background:#FF6B35;"
                    onmouseover="this.style.background='#E8521A'; this.style.boxShadow='0 2px 8px rgba(255,107,53,0.35)';"
                    onmouseout="this.style.background='#FF6B35'; this.style.boxShadow='';">
                    Guardar
                  </button>
                  <button
                    onclick='openEditOrder({{ json_encode([
                      "id"            => $order->id,
                      "order_number"  => $orderNum,
                      "total"         => (float) $order->total,
                      "table"         => $order->table?->name,
                      "table_id"      => $order->table_id,
                      "customer_name" => $order->customer_name,
                      "items"         => $order->items->map(fn($i) => [
                        "name"       => $i->menuItem?->name ?? "Producto eliminado",
                        "quantity"   => $i->quantity,
                        "unit_price" => $i->unit_price,
                        "subtotal"   => $i->subtotal,
                      ])->values()->toArray(),
                    ]) }})'
                    class="text-[11.5px] font-semibold px-3 py-1.5 rounded-lg transition-colors cursor-pointer flex-shrink-0"
                    style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;"
                    onmouseover="this.style.background='#dcfce7';"
                    onmouseout="this.style.background='#f0fdf4';">
                    Editar
                  </button>
                  <button
                    onclick='openOrderDetail({{ json_encode([
                      "id"               => $order->id,
                      "order_number"     => $orderNum,
                      "status"           => $order->status,
                      "total"            => $order->total,
                      "delivery_mode"    => $order->delivery_mode,
                      "delivery_address" => $order->delivery_address,
                      "delivery_zone"    => $order->deliveryZone?->name,
                      "delivery_fee"     => $order->delivery_fee,
                      "table"            => $order->table?->name,
                      "source"           => $order->source ?? 'app',
                      "notes"            => $order->notes,
                      "created_at"       => $order->created_at->format("d/m/Y H:i"),
                      "customer"         => $order->customer_name ?? $order->user?->name ?? "Cliente",
                      "customer_phone"   => $order->user?->phone ?? "",
                      "items"            => $order->items->map(fn($i) => [
                        "name"       => $i->menuItem?->name ?? "Producto eliminado",
                        "quantity"   => $i->quantity,
                        "unit_price" => $i->unit_price,
                        "subtotal"   => $i->subtotal,
                      ])->values()->toArray(),
                    ]) }})'
                    class="text-[11.5px] font-semibold text-slate-600 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer flex-shrink-0">
                    Ver
                  </button>
                </div>
              </td>

            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- ── PAGINATION ── --}}
    @if($orders->hasPages())
      <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100">
        <p class="text-[12px] text-slate-400">
          Página <strong class="text-slate-600">{{ $orders->currentPage() }}</strong>
          de <strong class="text-slate-600">{{ $orders->lastPage() }}</strong>
        </p>

        <div class="flex items-center gap-1.5">
          {{-- Previous --}}
          @if($orders->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-[12px] font-semibold border border-slate-100 text-slate-300 cursor-default">‹ Ant.</span>
          @else
            <a href="{{ $orders->previousPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-[12px] font-semibold border border-slate-200 text-slate-500 transition-colors"
               onmouseover="this.style.borderColor='#FF6B35'; this.style.color='#FF6B35';"
               onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
              ‹ Ant.
            </a>
          @endif

          {{-- Page numbers --}}
          @foreach($orders->getUrlRange(max(1, $orders->currentPage()-2), min($orders->lastPage(), $orders->currentPage()+2)) as $page => $url)
            @if($page == $orders->currentPage())
              <span class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-white"
                    style="background:#FF6B35;">{{ $page }}</span>
            @else
              <a href="{{ $url }}"
                 class="px-3 py-1.5 rounded-lg text-[12px] font-semibold border border-slate-200 text-slate-500 transition-colors"
                 onmouseover="this.style.borderColor='#FF6B35'; this.style.color='#FF6B35';"
                 onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                {{ $page }}
              </a>
            @endif
          @endforeach

          {{-- Next --}}
          @if($orders->hasMorePages())
            <a href="{{ $orders->nextPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-[12px] font-semibold border border-slate-200 text-slate-500 transition-colors"
               onmouseover="this.style.borderColor='#FF6B35'; this.style.color='#FF6B35';"
               onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
              Sig. ›
            </a>
          @else
            <span class="px-3 py-1.5 rounded-lg text-[12px] font-semibold border border-slate-100 text-slate-300 cursor-default">Sig. ›</span>
          @endif
        </div>
      </div>
    @endif

  @endif
</div>

@endif

{{-- ════════════════════════════════
     ORDER DETAIL MODAL
════════════════════════════════ --}}
<div id="orderDetailModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,0.45);"
     onclick="if(event.target===this) closeOrderDetail()">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
      <div>
        <div class="flex items-center gap-2">
          <h2 id="od-number" class="text-[16px] font-bold text-slate-800"></h2>
          <span id="od-source-badge" class="text-[10.5px] font-bold px-2 py-0.5 rounded-full"></span>
        </div>
        <p id="od-date" class="text-[12px] text-slate-400 mt-0.5"></p>
      </div>
      <div class="flex items-center gap-2">
        <span id="od-status-badge" class="text-[11px] font-bold px-2.5 py-1 rounded-full"></span>
        <button onclick="closeOrderDetail()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <div class="px-6 py-5 flex flex-col gap-5">

      {{-- Customer --}}
      <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
             style="background:linear-gradient(135deg,#FF6B35,#E8521A);" id="od-avatar"></div>
        <div>
          <p id="od-customer" class="text-[13px] font-bold text-slate-800"></p>
          <p id="od-phone" class="text-[12px] text-slate-400"></p>
        </div>
      </div>

      {{-- Table --}}
      <div id="od-table-wrap" class="hidden items-center justify-between gap-3 px-4 py-3 rounded-xl border border-orange-100" style="background:#fff8f5;">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="#FF6B35" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18"/>
          </svg>
          <span class="text-[13px] font-bold" style="color:#FF6B35;" id="od-table-name"></span>
        </div>
        <button id="od-release-btn" onclick="releaseTableFromDetail()"
                class="text-[11.5px] font-bold px-3 py-1.5 rounded-lg transition-colors flex-shrink-0"
                style="background:#fef2f2; color:#ef4444; border:1px solid #fecaca;"
                onmouseover="this.style.background='#fee2e2';"
                onmouseout="this.style.background='#fef2f2';">
          Liberar mesa
        </button>
      </div>

      {{-- Delivery info --}}
      <div id="od-delivery-wrap" class="flex flex-col gap-2">
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Entrega</p>
        <div class="flex items-start gap-2.5 text-[13px] text-slate-700">
          <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span id="od-address"></span>
        </div>
        <div id="od-notes-wrap" class="hidden flex items-start gap-2.5 text-[13px] text-slate-500 italic">
          <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
          <span id="od-notes"></span>
        </div>
      </div>

      {{-- Items --}}
      <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-3">Productos</p>
        <div id="od-items" class="flex flex-col gap-2"></div>
      </div>

      {{-- Totals --}}
      <div class="border-t border-slate-100 pt-4 flex flex-col gap-1.5">
        <div id="od-fee-row" class="hidden flex items-center justify-between text-[13px] text-slate-500">
          <span>Envío</span>
          <span id="od-fee"></span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-[14px] font-bold text-slate-800">Total</span>
          <span id="od-total" class="text-[18px] font-extrabold" style="color:#FF6B35;"></span>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- ════════════════════════════════
     EDIT ORDER MODAL
════════════════════════════════ --}}
<div id="editOrderModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,0.45);"
     onclick="if(event.target===this) closeEditOrder()">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 flex-shrink-0">
      <div>
        <h2 class="text-[16px] font-bold text-slate-800">Editar Pedido <span id="eo-number" class="font-mono text-slate-500"></span></h2>
        <p class="text-[12px] text-slate-400 mt-0.5">Agrega productos o libera la mesa</p>
      </div>
      <button onclick="closeEditOrder()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <div class="overflow-y-auto flex-1 px-6 py-5 flex flex-col gap-5">

      {{-- Current items --}}
      <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2">Productos actuales</p>
        <div id="eo-current-items" class="flex flex-col gap-1.5 text-[13px] text-slate-600 bg-slate-50 rounded-xl px-4 py-3"></div>
      </div>

      {{-- Table section --}}
      <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2">Mesa</p>
        <div id="eo-table-grid" class="flex flex-wrap gap-2"></div>
        <p id="eo-table-busy-msg" class="hidden text-[11px] text-red-500 mt-1.5">Esa mesa ya tiene un pedido activo hoy.</p>
      </div>

      {{-- Add products --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Agregar productos</p>
          <div class="flex gap-1.5 flex-wrap" id="eo-cat-tabs"></div>
        </div>
        <div id="eo-menu-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-52 overflow-y-auto pr-1"></div>
      </div>

      {{-- New items cart --}}
      <div id="eo-new-items-section" class="hidden">
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2">Productos a agregar</p>
        <div id="eo-new-items" class="flex flex-col gap-1.5"></div>
      </div>

    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between flex-shrink-0">
      <div>
        <span class="text-[12px] text-slate-400">Total actual: </span>
        <span id="eo-total" class="text-[15px] font-extrabold" style="color:#FF6B35;"></span>
        <span id="eo-total-new" class="text-[12px] text-emerald-600 font-bold hidden"></span>
      </div>
      <button id="eo-save-btn"
              onclick="saveOrderEdits()"
              class="px-5 py-2.5 rounded-xl text-[13px] font-bold text-white transition-all"
              style="background:#FF6B35;"
              onmouseover="this.style.background='#E8521A';"
              onmouseout="this.style.background='#FF6B35';">
        Guardar cambios
      </button>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  const badgeClasses = {
    pendiente:  'bg-amber-50 text-amber-700 border border-amber-200',
    preparando: 'bg-blue-50 text-blue-700 border border-blue-200',
    listo:      'bg-emerald-50 text-emerald-700 border border-emerald-200',
    entregado:  'bg-emerald-50 text-emerald-700 border border-emerald-200',
    cancelado:  'bg-red-50 text-red-700 border border-red-200',
    rechazado:  'bg-red-50 text-red-700 border border-red-200',
  };
  const badgeDots = {
    pendiente:  '#d97706',
    preparando: '#3b82f6',
    listo:      '#10b981',
    entregado:  '#10b981',
    cancelado:  '#ef4444',
    rechazado:  '#ef4444',
  };
  const statusLabels = {
    pendiente:  'Pendiente',
    preparando: 'Preparando',
    listo:      'Listo',
    entregado:  'Entregado',
    cancelado:  'Cancelado',
    rechazado:  'Rechazado',
  };

  function updateOrderStatus(orderId, url) {
    const select    = document.getElementById('select-' + orderId);
    const newStatus = select.value;
    const btn       = select.nextElementSibling;
    const origText  = btn.textContent;

    btn.disabled    = true;
    btn.textContent = '...';
    btn.style.opacity = '0.7';

    fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ status: newStatus }),
    })
    .then(r => r.json())
    .then(data => {
      btn.disabled      = false;
      btn.textContent   = origText;
      btn.style.opacity = '1';

      if (data.status) {
        const badge = document.getElementById('badge-' + orderId);
        const cls   = badgeClasses[data.status] || 'bg-amber-50 text-amber-700 border border-amber-200';
        const dot   = badgeDots[data.status] || '#d97706';
        const lbl   = statusLabels[data.status] || data.status;

        badge.className = 'text-[11px] font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1 ' + cls;
        badge.querySelector('.badge-dot').style.background = dot;
        badge.querySelector('.badge-text').textContent     = lbl;

        showToast(data.message || 'Estado actualizado', 'success');
      } else {
        showToast(data.message || 'Error al actualizar', 'error');
      }
    })
    .catch(() => {
      btn.disabled      = false;
      btn.textContent   = origText;
      btn.style.opacity = '1';
      showToast('Error de conexión', 'error');
    });
  }

  // ── Order detail modal ────────────────────────────────────
  const statusBadgeStyle = {
    pendiente:  'background:#fffbeb; color:#b45309; border:1px solid #fde68a;',
    preparando: 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;',
    listo:      'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;',
    entregado:  'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;',
    cancelado:  'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;',
    rechazado:  'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;',
  };
  const statusLabelMap = {
    pendiente:'Pendiente', preparando:'Preparando', listo:'Listo',
    entregado:'Entregado', cancelado:'Cancelado', rechazado:'Rechazado',
  };

  let currentDetailOrderId = null;

  function openOrderDetail(order) {
    currentDetailOrderId = order.id;
    document.getElementById('od-number').textContent    = order.order_number;
    document.getElementById('od-date').textContent      = order.created_at;
    document.getElementById('od-customer').textContent  = order.customer;
    document.getElementById('od-phone').textContent     = order.customer_phone || '';
    document.getElementById('od-avatar').textContent    = (order.customer || '?')[0].toUpperCase();
    document.getElementById('od-total').textContent     = 'L. ' + parseFloat(order.total).toFixed(2);

    // Status badge
    const badge = document.getElementById('od-status-badge');
    badge.textContent = statusLabelMap[order.status] || order.status;
    badge.setAttribute('style', statusBadgeStyle[order.status] || '');

    // Source badge
    const srcBadge = document.getElementById('od-source-badge');
    if (order.source === 'local') {
      srcBadge.textContent = '🪑 Local';
      srcBadge.setAttribute('style', 'background:#fff5f0; color:#FF6B35; border:1px solid #fed7c3;');
    } else {
      srcBadge.textContent = '📱 App';
      srcBadge.setAttribute('style', 'background:#eff6ff; color:#3b82f6; border:1px solid #bfdbfe;');
    }

    // Table
    const tableWrap = document.getElementById('od-table-wrap');
    if (order.table) {
      document.getElementById('od-table-name').textContent = order.table;
      tableWrap.classList.remove('hidden');
      tableWrap.style.display = 'flex';
    } else {
      tableWrap.classList.add('hidden');
      tableWrap.style.display = 'none';
    }

    // Address
    let addressText;
    if (order.delivery_mode === 'pickup') {
      addressText = '🏪 Para recoger en el local';
    } else if (order.delivery_zone) {
      addressText = '📍 Zona: ' + order.delivery_zone + (order.delivery_address ? ' — ' + order.delivery_address : '');
    } else if (order.delivery_address) {
      addressText = '📍 ' + order.delivery_address;
    } else {
      addressText = 'Sin dirección registrada';
    }
    document.getElementById('od-address').textContent = addressText;

    // Notes
    if (order.notes) {
      document.getElementById('od-notes').textContent = order.notes;
      document.getElementById('od-notes-wrap').classList.remove('hidden');
      document.getElementById('od-notes-wrap').style.display = 'flex';
    } else {
      document.getElementById('od-notes-wrap').classList.add('hidden');
      document.getElementById('od-notes-wrap').style.display = 'none';
    }

    // Delivery fee
    if (order.delivery_fee && parseFloat(order.delivery_fee) > 0) {
      document.getElementById('od-fee').textContent = 'L. ' + parseFloat(order.delivery_fee).toFixed(2);
      document.getElementById('od-fee-row').classList.remove('hidden');
      document.getElementById('od-fee-row').style.display = 'flex';
    } else {
      document.getElementById('od-fee-row').classList.add('hidden');
    }

    // Items
    const container = document.getElementById('od-items');
    container.innerHTML = '';
    if (order.items && order.items.length) {
      order.items.forEach(item => {
        container.innerHTML += `
          <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
            <div class="flex items-center gap-2.5">
              <span class="w-6 h-6 rounded-md bg-orange-50 text-orange-600 text-[11px] font-bold flex items-center justify-center flex-shrink-0">${item.quantity}</span>
              <span class="text-[13px] text-slate-700 font-medium">${item.name}</span>
            </div>
            <span class="text-[13px] font-bold text-slate-800 flex-shrink-0">L. ${parseFloat(item.subtotal).toFixed(2)}</span>
          </div>`;
      });
    } else {
      container.innerHTML = '<p class="text-[13px] text-slate-400 italic">Sin items registrados.</p>';
    }

    const modal = document.getElementById('orderDetailModal');
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function releaseTableFromDetail() {
    const btn = document.getElementById('od-release-btn');
    btn.disabled    = true;
    btn.textContent = 'Liberando…';

    fetch(EDIT_ORDERS_BASE_URL + '/' + currentDetailOrderId, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
      body: JSON.stringify({ release_table: true }),
    })
    .then(r => r.json())
    .then(data => {
      if (data.total !== undefined) {
        showToast('✅ Mesa liberada correctamente.', 'success');
        document.getElementById('od-table-wrap').classList.add('hidden');
        document.getElementById('od-table-wrap').style.display = 'none';
      } else {
        btn.disabled    = false;
        btn.textContent = 'Liberar mesa';
        showToast(data.message || 'Error al liberar.', 'error');
      }
    })
    .catch(() => {
      btn.disabled    = false;
      btn.textContent = 'Liberar mesa';
      showToast('Error de conexión.', 'error');
    });
  }

  function closeOrderDetail() {
    const modal = document.getElementById('orderDetailModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  // ── Edit order modal ──────────────────────────────────────
  const EDIT_ORDERS_BASE_URL = '{{ url('admin/orders') }}';

  @php
    $menuCategoriesData = $restaurant
      ? $restaurant->categories
          ->filter(fn($c) => $c->items->isNotEmpty())
          ->map(fn($c) => [
              'id'    => $c->id,
              'name'  => $c->name,
              'items' => $c->items->map(fn($i) => [
                  'id'    => $i->id,
                  'name'  => $i->name,
                  'price' => (float) $i->price,
                  'emoji' => $i->emoji ?? '🍽️',
              ])->values()->all(),
          ])->values()->all()
      : [];
  @endphp
  const MENU_CATEGORIES = @json($menuCategoriesData);

  @php
    $tablesData = isset($tables) ? $tables->map(fn($t) => [
        'id'   => $t->id,
        'name' => $t->name,
        'busy' => isset($busyTableIds) && $busyTableIds->contains($t->id),
    ])->values()->all() : [];
  @endphp
  const ALL_TABLES = @json($tablesData);

  let editOrderId         = null;
  let editOrderTotal      = 0;
  let editTableReleased   = false;
  let editSelectedTableId = null;  // null = sin cambio, 0 = liberar, N = asignar mesa N
  let editCart            = {};    // { menuItemId: {id, name, price, emoji, qty} }

  function openEditOrder(order) {
    editOrderId       = order.id;
    editOrderTotal    = parseFloat(order.total);
    editTableReleased = false;
    editCart          = {};

    document.getElementById('eo-number').textContent = order.order_number;
    document.getElementById('eo-total').textContent  = 'L. ' + editOrderTotal.toFixed(2);
    document.getElementById('eo-total-new').classList.add('hidden');

    // Current items
    const curEl = document.getElementById('eo-current-items');
    if (order.items && order.items.length) {
      curEl.innerHTML = order.items.map(i =>
        `<div class="flex items-center justify-between py-1 border-b border-slate-100 last:border-0">
          <span>${i.quantity}× ${i.name}</span>
          <span class="font-semibold">L. ${parseFloat(i.subtotal).toFixed(2)}</span>
        </div>`
      ).join('');
    } else {
      curEl.innerHTML = '<p class="text-slate-400 italic text-[13px]">Sin productos.</p>';
    }

    // Table grid
    editSelectedTableId = null;
    renderEditTableGrid(order.table_id);

    // Build menu grid
    renderEditMenu('all');

    // Build category tabs
    const tabsEl = document.getElementById('eo-cat-tabs');
    tabsEl.innerHTML = '';
    const allBtn = document.createElement('button');
    allBtn.dataset.cat = 'all';
    allBtn.textContent = 'Todos';
    allBtn.className = 'eo-cat-tab px-2.5 py-1 rounded-full text-[11px] font-semibold border transition-all';
    allBtn.setAttribute('style', 'background:#FF6B35; color:#fff; border-color:#FF6B35;');
    allBtn.onclick = () => { renderEditMenu('all'); updateEoCatTabs('all'); };
    tabsEl.appendChild(allBtn);
    MENU_CATEGORIES.forEach(cat => {
      const btn = document.createElement('button');
      btn.dataset.cat = cat.id;
      btn.textContent = cat.name;
      btn.className = 'eo-cat-tab px-2.5 py-1 rounded-full text-[11px] font-semibold border border-slate-200 bg-white text-slate-600 transition-all';
      btn.onclick = () => { renderEditMenu(cat.id); updateEoCatTabs(cat.id); };
      tabsEl.appendChild(btn);
    });

    document.getElementById('eo-new-items-section').classList.add('hidden');
    renderEditNewItems();

    const modal = document.getElementById('editOrderModal');
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function updateEoCatTabs(activeCat) {
    document.querySelectorAll('.eo-cat-tab').forEach(btn => {
      const isActive = String(btn.dataset.cat) === String(activeCat);
      btn.setAttribute('style', isActive
        ? 'background:#FF6B35; color:#fff; border-color:#FF6B35;'
        : 'background:#fff; color:#64748b; border-color:#e2e8f0;');
    });
  }

  function renderEditMenu(catFilter) {
    const grid = document.getElementById('eo-menu-grid');
    let items = [];
    MENU_CATEGORIES.forEach(cat => {
      if (catFilter === 'all' || cat.id === catFilter) {
        items = items.concat(cat.items);
      }
    });

    if (items.length === 0) {
      grid.innerHTML = '<p class="text-[13px] text-slate-400 col-span-3 py-4 text-center">Sin productos disponibles.</p>';
      return;
    }

    grid.innerHTML = items.map(item => `
      <div class="bg-slate-50 rounded-xl p-3 flex flex-col gap-2 border border-slate-100 hover:border-orange-200 transition-colors">
        <div class="flex items-center gap-2">
          <span class="text-2xl">${item.emoji}</span>
          <div class="flex-1 min-w-0">
            <p class="text-[12px] font-semibold text-slate-700 line-clamp-1">${item.name}</p>
            <p class="text-[11px] font-bold" style="color:#FF6B35;">L. ${item.price.toFixed(2)}</p>
          </div>
        </div>
        <button onclick="addEditItem(${item.id}, ${JSON.stringify(item.name)}, ${item.price}, ${JSON.stringify(item.emoji)})"
                class="w-full py-1 rounded-lg text-[11px] font-bold text-white transition-all"
                style="background:#FF6B35;"
                onmouseover="this.style.background='#E8521A';"
                onmouseout="this.style.background='#FF6B35';">
          + Agregar
        </button>
      </div>
    `).join('');
  }

  function addEditItem(id, name, price, emoji) {
    editCart[id] ? editCart[id].qty++ : (editCart[id] = { id, name, price, emoji, qty: 1 });
    renderEditNewItems();
    updateEditTotal();
  }

  function removeEditItem(id) {
    delete editCart[id];
    renderEditNewItems();
    updateEditTotal();
  }

  function decrementEditItem(id) {
    if (!editCart[id]) return;
    editCart[id].qty--;
    if (editCart[id].qty <= 0) delete editCart[id];
    renderEditNewItems();
    updateEditTotal();
  }

  function renderEditNewItems() {
    const section = document.getElementById('eo-new-items-section');
    const el      = document.getElementById('eo-new-items');
    const items   = Object.values(editCart);

    if (items.length === 0) {
      section.classList.add('hidden');
      return;
    }

    section.classList.remove('hidden');
    el.innerHTML = items.map(item => `
      <div class="flex items-center gap-3 py-1.5 border-b border-slate-50 last:border-0">
        <span class="text-lg">${item.emoji}</span>
        <div class="flex-1 min-w-0">
          <p class="text-[12px] font-semibold text-slate-700 line-clamp-1">${item.name}</p>
          <p class="text-[11px] text-slate-400">L. ${item.price.toFixed(2)}</p>
        </div>
        <div class="flex items-center gap-1">
          <button onclick="decrementEditItem(${item.id})"
                  class="w-6 h-6 rounded-md flex items-center justify-center text-slate-500"
                  style="background:#f1f5f9;"
                  onmouseover="this.style.background='#e2e8f0';"
                  onmouseout="this.style.background='#f1f5f9';">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
          </button>
          <span class="w-6 text-center text-[12px] font-bold">${item.qty}</span>
          <button onclick="addEditItem(${item.id}, ${JSON.stringify(item.name)}, ${item.price}, ${JSON.stringify(item.emoji)})"
                  class="w-6 h-6 rounded-md flex items-center justify-center text-white"
                  style="background:#FF6B35;"
                  onmouseover="this.style.background='#E8521A';"
                  onmouseout="this.style.background='#FF6B35';">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          </button>
        </div>
        <button onclick="removeEditItem(${item.id})"
                class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400"
                style="background:#f1f5f9;"
                onmouseover="this.style.background='#fee2e2'; this.style.color='#ef4444';"
                onmouseout="this.style.background='#f1f5f9'; this.style.color='#94a3b8';">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    `).join('');
  }

  function updateEditTotal() {
    const added    = Object.values(editCart).reduce((s, i) => s + i.price * i.qty, 0);
    const newTotal = editOrderTotal + added;
    const newEl    = document.getElementById('eo-total-new');
    if (added > 0) {
      newEl.textContent = ` → L. ${newTotal.toFixed(2)} (+L. ${added.toFixed(2)})`;
      newEl.classList.remove('hidden');
    } else {
      newEl.classList.add('hidden');
    }
  }

  function renderEditTableGrid(currentTableId) {
    const grid = document.getElementById('eo-table-grid');
    document.getElementById('eo-table-busy-msg').classList.add('hidden');

    if (ALL_TABLES.length === 0) {
      grid.innerHTML = '<p class="text-[12px] text-slate-400 italic">Sin mesas configuradas.</p>';
      return;
    }

    // "Sin mesa" button
    let html = `<button type="button" onclick="selectEditTable(0, this)"
      data-tid="0"
      class="eo-table-btn px-3 py-2 rounded-xl border-2 text-[12px] font-bold transition-all"
      style="${currentTableId === null || currentTableId === undefined
        ? 'border-color:#FF6B35; background:#fff5f0; color:#FF6B35;'
        : 'border-color:#e2e8f0; background:#fff; color:#475569;'}">
      🥡 Sin mesa
    </button>`;

    ALL_TABLES.forEach(t => {
      const isCurrent = t.id === currentTableId;
      const isBusy    = t.busy && !isCurrent;
      html += `<button type="button"
        ${isBusy ? 'disabled' : `onclick="selectEditTable(${t.id}, this)"`}
        data-tid="${t.id}"
        class="eo-table-btn relative px-3 py-2 rounded-xl border-2 text-[12px] font-bold transition-all ${isBusy ? 'cursor-not-allowed' : 'cursor-pointer'}"
        style="${isCurrent
          ? 'border-color:#FF6B35; background:#fff5f0; color:#FF6B35;'
          : isBusy
            ? 'border-color:#fecaca; background:#fef2f2; color:#f87171;'
            : 'border-color:#e2e8f0; background:#fff; color:#475569;'}"
        ${!isBusy && !isCurrent ? `onmouseover="this.style.borderColor='#FF6B35'; this.style.color='#FF6B35';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569';"` : ''}>
        ${t.name}
        ${isBusy ? '<span class="absolute -top-1.5 -right-1.5 w-3 h-3 rounded-full bg-red-400 border-2 border-white"></span>' : ''}
      </button>`;
    });

    grid.innerHTML = html;
  }

  function selectEditTable(id, btn) {
    editSelectedTableId = id;   // 0 = sin mesa/liberar, N = mesa N
    document.getElementById('eo-table-busy-msg').classList.add('hidden');

    document.querySelectorAll('.eo-table-btn').forEach(b => {
      b.style.borderColor = '#e2e8f0';
      b.style.background  = '#fff';
      b.style.color       = '#475569';
    });
    btn.style.borderColor = '#FF6B35';
    btn.style.background  = '#fff5f0';
    btn.style.color       = '#FF6B35';
  }

  function saveOrderEdits() {
    const newItems = Object.values(editCart).map(i => ({ id: i.id, qty: i.qty }));
    const tableChanged = editSelectedTableId !== null;

    if (newItems.length === 0 && !tableChanged) {
      showToast('No hay cambios para guardar.', 'error');
      return;
    }

    const body = {};
    if (newItems.length)  body.new_items     = newItems;
    if (tableChanged) {
      if (editSelectedTableId === 0) body.release_table = true;
      else                           body.table_id      = editSelectedTableId;
    }

    const btn = document.getElementById('eo-save-btn');
    btn.disabled    = true;
    btn.textContent = 'Guardando…';
    btn.style.background = '#94a3b8';

    fetch(EDIT_ORDERS_BASE_URL + '/' + editOrderId, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify(body),
    })
    .then(r => r.json())
    .then(data => {
      btn.disabled    = false;
      btn.textContent = 'Guardar cambios';
      btn.style.background = '#FF6B35';

      if (data.total !== undefined) {
        showToast('✅ ' + data.message, 'success');
        const row = document.getElementById('order-row-' + editOrderId);
        if (row) {
          const totalCell = row.querySelector('td:nth-child(4) span');
          if (totalCell) totalCell.textContent = 'L.\u00a0' + parseFloat(data.total).toFixed(2);
        }
        closeEditOrder();
      } else {
        // Could be a 422 for busy table
        if (data.message && data.message.includes('mesa')) {
          document.getElementById('eo-table-busy-msg').classList.remove('hidden');
          editSelectedTableId = null;
          renderEditTableGrid(null);
        }
        showToast(data.message || 'Error al guardar.', 'error');
      }
    })
    .catch(() => {
      btn.disabled    = false;
      btn.textContent = 'Guardar cambios';
      btn.style.background = '#FF6B35';
      showToast('Error de conexión.', 'error');
    });
  }

  function closeEditOrder() {
    const modal = document.getElementById('editOrderModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    editOrderId         = null;
    editCart            = {};
    editSelectedTableId = null;
  }
</script>
@endpush
