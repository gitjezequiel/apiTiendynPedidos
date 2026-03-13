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
</script>
@endpush
