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
  @if(isset($orders))
    <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-white border border-slate-200 shadow-sm">
      <span class="text-[12px] text-slate-500 font-medium">Total:</span>
      <span class="text-[15px] font-bold text-slate-800">{{ $orders->total() }}</span>
      <span class="text-[11px] text-slate-400">pedidos</span>
    </div>
  @endif
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
                    <p class="text-[11px] text-slate-400">#{{ $order->id }}</p>
                  </div>
                </div>
              </td>

              {{-- Order number --}}
              <td class="px-5 py-3.5">
                <span class="text-[12px] text-slate-500 font-medium font-mono">{{ $orderNum }}</span>
              </td>

              {{-- Items summary --}}
              <td class="px-5 py-3.5">
                @if($order->items && $order->items->count())
                  <span class="text-[12px] text-slate-500">
                    {{ $order->items->count() }} ítem{{ $order->items->count() != 1 ? 's' : '' }}
                  </span>
                @else
                  <span class="text-slate-300">—</span>
                @endif
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
                    onclick='openOrderDetail({{ json_encode([
                      "id"               => $order->id,
                      "order_number"     => $orderNum,
                      "status"           => $order->status,
                      "total"            => $order->total,
                      "delivery_mode"    => $order->delivery_mode,
                      "delivery_address" => $order->delivery_address,
                      "delivery_zone"    => $order->deliveryZone?->name,
                      "delivery_fee"     => $order->delivery_fee,
                      "notes"            => $order->notes,
                      "created_at"       => $order->created_at->format("d/m/Y H:i"),
                      "customer"         => $order->user?->name ?? "Cliente",
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
        <h2 id="od-number" class="text-[16px] font-bold text-slate-800"></h2>
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

  function openOrderDetail(order) {
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

  function closeOrderDetail() {
    const modal = document.getElementById('orderDetailModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }
</script>
@endpush
