@extends('admin.layouts.app')

@section('title', 'Tomar Pedido — TiendynFood Admin')
@section('topbar-title', 'Tomar Pedido')

@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Nuevo Pedido en Local</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">Selecciona la mesa, los productos y confirma</p>
  </div>
  <a href="{{ route('admin.orders') }}"
     class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[13px] font-semibold border border-slate-200 bg-white text-slate-600 transition-all"
     onmouseover="this.style.borderColor='#FF6B35'; this.style.color='#FF6B35';"
     onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
  </a>
</div>

@if (!$restaurant)
  <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-base">⚠️</div>
    <div>
      <p class="text-sm font-semibold text-amber-800">Sin restaurante registrado</p>
      <p class="text-[13px] text-amber-700 mt-0.5">No tienes un restaurante en el sistema.</p>
    </div>
  </div>
@else

{{-- ══════════════════════════════════════
     MESA SELECTOR
══════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-5 p-5">
  <div class="flex items-center justify-between mb-4">
    <div>
      <p class="text-[14px] font-bold text-slate-800">Tipo de pedido</p>
      <p class="text-[12px] text-slate-400 mt-0.5">Selecciona "Para llevar" o elige una mesa. Las mesas en rojo tienen pedido activo.</p>
    </div>
    <button onclick="openTableModal()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-bold text-white transition-all"
            style="background:#FF6B35;"
            onmouseover="this.style.background='#E8521A';"
            onmouseout="this.style.background='#FF6B35';">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
      </svg>
      Gestionar Mesas
    </button>
  </div>

  @php $busyIds = $busyTableIds->toArray(); @endphp

  @if($tables->isEmpty())
    <div class="py-6 text-center border border-dashed border-slate-200 rounded-xl">
      <p class="text-[13px] text-slate-400">No tienes mesas configuradas.</p>
      <button onclick="openTableModal()"
              class="mt-2 text-[12px] font-bold underline" style="color:#FF6B35;">
        Agregar mesas ahora
      </button>
    </div>
  @else
    <div class="flex flex-wrap gap-2.5" id="table-grid">
      {{-- Para llevar --}}
      <button type="button"
              onclick="selectTable(null, this)"
              data-table-id=""
              data-table-name="para-llevar"
              class="table-btn active-table px-4 py-2.5 rounded-xl border-2 text-[13px] font-bold transition-all"
              style="border-color:#FF6B35; background:#fff5f0; color:#FF6B35;">
        🥡 Para llevar
      </button>

      @foreach($tables as $t)
        @php $busy = in_array($t->id, $busyIds); @endphp
        <button type="button"
                onclick="{{ $busy ? 'void(0)' : "selectTable($t->id, this)" }}"
                data-table-id="{{ $t->id }}"
                data-table-name="{{ $t->name }}"
                @if($busy) disabled @endif
                class="table-btn relative px-4 py-2.5 rounded-xl border-2 text-[13px] font-bold transition-all {{ $busy ? 'cursor-not-allowed' : 'cursor-pointer' }}"
                style="{{ $busy
                  ? 'border-color:#fecaca; background:#fef2f2; color:#f87171;'
                  : 'border-color:#e2e8f0; background:#fff; color:#475569;' }}"
                @if(!$busy)
                  onmouseover="if(!this.classList.contains('active-table')) { this.style.borderColor='#FF6B35'; this.style.color='#FF6B35'; }"
                  onmouseout="if(!this.classList.contains('active-table')) { this.style.borderColor='#e2e8f0'; this.style.color='#475569'; }"
                @endif>
          {{ $t->name }}
          @if($busy)
            <span class="absolute -top-1.5 -right-1.5 w-3 h-3 rounded-full bg-red-400 border-2 border-white"></span>
          @endif
        </button>
      @endforeach
    </div>
  @endif

  {{-- Selected table display --}}
  <div id="selected-table-display" class="mt-3 hidden">
    <span class="text-[12px] text-slate-500">Mesa seleccionada: </span>
    <span id="selected-table-name" class="text-[12px] font-bold" style="color:#FF6B35;"></span>
  </div>
  <div id="selected-takeaway-display" class="mt-3 hidden">
    <span class="text-[12px] font-bold" style="color:#FF6B35;">🥡 Pedido para llevar (sin mesa)</span>
  </div>
</div>

{{-- ══════════════════════════════════════
     MENU + CART
══════════════════════════════════════ --}}
<div class="flex gap-5" id="pos-layout">

  {{-- ── LEFT: MENU ── --}}
  <div class="flex-1 min-w-0">

    @php $allItems = $restaurant->categories->flatMap->items; @endphp

    @if($restaurant->categories->isEmpty() || $allItems->isEmpty())
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-4">🍽️</div>
        <p class="text-[15px] font-bold text-slate-700 mb-1">Sin productos disponibles</p>
        <p class="text-[13px] text-slate-400">Agrega productos al menú antes de tomar pedidos.</p>
      </div>
    @else

      {{-- Category tabs --}}
      <div class="flex gap-2 flex-wrap mb-4" id="cat-tabs">
        <button onclick="filterCat('all')" data-cat="all" class="cat-tab px-4 py-1.5 rounded-full text-[12.5px] font-semibold border transition-all">
          Todos
        </button>
        @foreach($restaurant->categories as $cat)
          @if($cat->items->isNotEmpty())
            <button onclick="filterCat({{ $cat->id }})" data-cat="{{ $cat->id }}"
                    class="cat-tab px-4 py-1.5 rounded-full text-[12.5px] font-semibold border border-slate-200 bg-white text-slate-600 transition-all">
              {{ $cat->name }}
            </button>
          @endif
        @endforeach
      </div>

      {{-- Items grid --}}
      <div class="grid grid-cols-2 xl:grid-cols-3 gap-3" id="items-grid">
        @foreach($restaurant->categories as $cat)
          @foreach($cat->items as $item)
            <div class="item-card bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col gap-3 cursor-pointer select-none transition-all"
                 data-cat="{{ $cat->id }}"
                 data-id="{{ $item->id }}"
                 data-name="{{ $item->name }}"
                 data-price="{{ $item->price }}"
                 data-emoji="{{ $item->emoji ?? '' }}"
                 onclick="addItem(this)"
                 onmouseover="this.style.borderColor='#FF6B35'; this.style.boxShadow='0 4px 14px rgba(255,107,53,0.12)';"
                 onmouseout="this.style.borderColor='#f1f5f9'; this.style.boxShadow='';">
              <div class="w-full h-28 rounded-xl overflow-hidden flex items-center justify-center" style="background:#fff8f5;">
                @if($item->image_url)
                  <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                @else
                  <span class="text-4xl">{{ $item->emoji ?? '🍽️' }}</span>
                @endif
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[13px] font-bold text-slate-800 leading-tight line-clamp-2">{{ $item->name }}</p>
                <p class="text-[14px] font-extrabold mt-1" style="color:#FF6B35;">L. {{ number_format($item->price, 2) }}</p>
              </div>
              <button class="w-full py-1.5 rounded-lg text-[12px] font-bold text-white"
                      style="background:#FF6B35;"
                      onmouseover="event.stopPropagation(); this.style.background='#E8521A';"
                      onmouseout="this.style.background='#FF6B35';"
                      onclick="event.stopPropagation(); addItem(this.closest('.item-card'))">
                + Agregar
              </button>
            </div>
          @endforeach
        @endforeach
      </div>

    @endif
  </div>

  {{-- ── RIGHT: CART ── --}}
  <div class="w-80 flex-shrink-0">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm sticky top-0">

      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="#FF6B35" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          <span class="text-[14px] font-bold text-slate-800">Pedido</span>
        </div>
        <span id="cart-count" class="text-[11px] font-bold px-2 py-0.5 rounded-full text-white hidden" style="background:#FF6B35;">0</span>
      </div>

      <div id="cart-items" class="px-4 py-3 flex flex-col gap-2 max-h-[320px] overflow-y-auto">
        <div id="cart-empty" class="py-8 text-center">
          <p class="text-[13px] text-slate-400">Agrega productos al pedido</p>
        </div>
      </div>

      <div class="px-5 pb-3 pt-3 border-t border-slate-50">
        <label id="customer-name-label" class="text-[11px] font-bold text-slate-400 uppercase tracking-wide block mb-1.5">Nombre del cliente</label>
        <input id="order-customer-name" type="text" maxlength="100" placeholder="Ej: Juan García…"
               class="w-full text-[13px] border border-slate-200 rounded-xl px-3 py-2 outline-none text-slate-700 placeholder-slate-300 transition-colors"
               onfocus="this.style.borderColor='#FF6B35';"
               onblur="this.style.borderColor='#e2e8f0';">
      </div>

      <div class="px-5 pb-3 pt-1">
        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wide block mb-1.5">Notas adicionales</label>
        <textarea id="order-notes" rows="2" maxlength="255" placeholder="Ej: sin cebolla, bien cocido…"
                  class="w-full text-[13px] border border-slate-200 rounded-xl px-3 py-2 outline-none resize-none text-slate-700 placeholder-slate-300 transition-colors"
                  onfocus="this.style.borderColor='#FF6B35';"
                  onblur="this.style.borderColor='#e2e8f0';"></textarea>
      </div>

      <div class="px-5 py-4 border-t border-slate-100">
        <div class="flex items-center justify-between mb-4">
          <span class="text-[14px] font-bold text-slate-800">Total</span>
          <span id="cart-total" class="text-[20px] font-extrabold" style="color:#FF6B35;">L. 0.00</span>
        </div>
        <button id="confirm-btn"
                onclick="confirmOrder()"
                disabled
                class="w-full py-3 rounded-xl text-[14px] font-bold text-white transition-all cursor-not-allowed"
                style="background:#cbd5e1;">
          Confirmar Pedido
        </button>
      </div>

    </div>
  </div>

</div>

@endif

{{-- ══════════════════════════════════════
     MANAGE TABLES MODAL
══════════════════════════════════════ --}}
<div id="tableModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,0.45);"
     onclick="if(event.target===this) closeTableModal()">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">

    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h2 class="text-[15px] font-bold text-slate-800">Gestionar Mesas</h2>
      <button onclick="closeTableModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="px-6 py-4">
      {{-- Add table form --}}
      <div class="flex gap-2 mb-4">
        <input id="new-table-name" type="text" maxlength="50" placeholder="Ej: Mesa 1, Barra, Terraza…"
               class="flex-1 text-[13px] border border-slate-200 rounded-xl px-3 py-2 outline-none text-slate-700 placeholder-slate-300 transition-colors"
               onfocus="this.style.borderColor='#FF6B35';"
               onblur="this.style.borderColor='#e2e8f0';"
               onkeydown="if(event.key==='Enter') addTable()">
        <button onclick="addTable()"
                class="px-4 py-2 rounded-xl text-[13px] font-bold text-white transition-all flex-shrink-0"
                style="background:#FF6B35;"
                onmouseover="this.style.background='#E8521A';"
                onmouseout="this.style.background='#FF6B35';">
          Agregar
        </button>
      </div>

      {{-- Tables list --}}
      <div id="modal-tables-list" class="flex flex-col gap-2 max-h-64 overflow-y-auto">
        @forelse($tables as $t)
          <div id="modal-table-{{ $t->id }}" class="flex items-center justify-between py-2 px-3 rounded-xl bg-slate-50">
            <span class="text-[13px] font-semibold text-slate-700">{{ $t->name }}</span>
            <button onclick="deleteTable({{ $t->id }})"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 transition-colors"
                    style="background:#f1f5f9;"
                    onmouseover="this.style.background='#fee2e2'; this.style.color='#ef4444';"
                    onmouseout="this.style.background='#f1f5f9'; this.style.color='#94a3b8';">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        @empty
          <p id="modal-empty-msg" class="text-[13px] text-slate-400 text-center py-4">Aún no hay mesas. Agrega la primera.</p>
        @endforelse
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
  const STORE_URL        = '{{ route('admin.orders.store') }}';
  const TABLES_STORE_URL = '{{ route('admin.tables.store') }}';
  const TABLES_BASE_URL  = '{{ url('admin/tables') }}';
  const csrfToken        = document.querySelector('meta[name="csrf-token"]').content;

  // ── Table selection ───────────────────────────────────────
  let selectedTableId   = null;
  let selectedTableName = null;

  function selectTable(id, btn) {
    selectedTableId   = id;
    selectedTableName = id ? btn.dataset.tableName : null;

    document.querySelectorAll('.table-btn').forEach(b => {
      if (b.dataset.tableId !== '' || id === null) {
        b.classList.remove('active-table');
        b.style.borderColor = '#e2e8f0';
        b.style.background  = '#fff';
        b.style.color       = '#475569';
      }
    });

    btn.classList.add('active-table');
    btn.style.borderColor = '#FF6B35';
    btn.style.background  = '#fff5f0';
    btn.style.color       = '#FF6B35';

    const display    = document.getElementById('selected-table-display');
    const takeaway   = document.getElementById('selected-takeaway-display');
    const nameEl     = document.getElementById('selected-table-name');
    const nameLabel  = document.getElementById('customer-name-label');
    if (id) {
      nameEl.textContent = selectedTableName;
      display.classList.remove('hidden');
      takeaway.classList.add('hidden');
      nameLabel.textContent = 'Nombre del cliente en mesa';
    } else {
      display.classList.add('hidden');
      takeaway.classList.remove('hidden');
      nameLabel.textContent = 'Nombre del cliente';
    }
  }

  // Init: "Sin mesa" already active
  document.querySelector('.table-btn[data-table-id=""]')?.click?.();

  // ── Category filter ───────────────────────────────────────
  function filterCat(cat) {
    document.querySelectorAll('.item-card').forEach(card => {
      card.style.display = (cat === 'all' || parseInt(card.dataset.cat) === parseInt(cat)) ? '' : 'none';
    });
    document.querySelectorAll('.cat-tab').forEach(btn => {
      const active = String(btn.dataset.cat) === String(cat);
      btn.style.background  = active ? '#FF6B35' : '#fff';
      btn.style.color       = active ? '#fff'    : '#64748b';
      btn.style.borderColor = active ? '#FF6B35' : '#e2e8f0';
    });
  }
  document.querySelectorAll('.cat-tab').forEach(btn => {
    const isAll = btn.dataset.cat === 'all';
    btn.style.background  = isAll ? '#FF6B35' : '#fff';
    btn.style.color       = isAll ? '#fff'    : '#64748b';
    btn.style.borderColor = isAll ? '#FF6B35' : '#e2e8f0';
  });

  // ── Cart ─────────────────────────────────────────────────
  let cart = {};

  function addItem(card) {
    const id    = parseInt(card.dataset.id);
    const name  = card.dataset.name;
    const price = parseFloat(card.dataset.price);
    const emoji = card.dataset.emoji || '🍽️';
    cart[id] ? cart[id].qty++ : (cart[id] = { id, name, price, emoji, qty: 1 });
    renderCart();
  }

  function decrementItem(id) {
    if (!cart[id]) return;
    cart[id].qty--;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
  }

  function removeItem(id) {
    delete cart[id];
    renderCart();
  }

  function addItemById(id) {
    if (cart[id]) { cart[id].qty++; renderCart(); }
  }

  function renderCart() {
    const items    = Object.values(cart);
    const total    = items.reduce((s, i) => s + i.price * i.qty, 0);
    const count    = items.reduce((s, i) => s + i.qty, 0);
    const container = document.getElementById('cart-items');
    const countBadge = document.getElementById('cart-count');
    const totalEl   = document.getElementById('cart-total');
    const confirmBtn = document.getElementById('confirm-btn');

    totalEl.textContent = 'L. ' + total.toFixed(2);

    if (count > 0) {
      countBadge.textContent = count;
      countBadge.classList.remove('hidden');
      confirmBtn.disabled = false;
      confirmBtn.style.background = '#FF6B35';
      confirmBtn.style.cursor     = 'pointer';
      confirmBtn.style.boxShadow  = '0 4px 14px rgba(255,107,53,0.3)';
    } else {
      countBadge.classList.add('hidden');
      confirmBtn.disabled = true;
      confirmBtn.style.background = '#cbd5e1';
      confirmBtn.style.cursor     = 'not-allowed';
      confirmBtn.style.boxShadow  = '';
    }

    if (items.length === 0) {
      container.innerHTML = '<div class="py-8 text-center"><p class="text-[13px] text-slate-400">Agrega productos al pedido</p></div>';
      return;
    }

    container.innerHTML = items.map(item => `
      <div class="flex items-center gap-3 py-2 border-b border-slate-50 last:border-0">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0" style="background:#fff8f5;">${item.emoji}</div>
        <div class="flex-1 min-w-0">
          <p class="text-[12px] font-semibold text-slate-700 leading-tight line-clamp-1">${item.name}</p>
          <p class="text-[11px] text-slate-400">L. ${item.price.toFixed(2)}</p>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button onclick="decrementItem(${item.id})"
                  class="w-6 h-6 rounded-md flex items-center justify-center text-slate-500"
                  style="background:#f1f5f9;"
                  onmouseover="this.style.background='#e2e8f0';"
                  onmouseout="this.style.background='#f1f5f9';">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
          </button>
          <span class="w-6 text-center text-[13px] font-bold text-slate-700">${item.qty}</span>
          <button onclick="addItemById(${item.id})"
                  class="w-6 h-6 rounded-md flex items-center justify-center text-white"
                  style="background:#FF6B35;"
                  onmouseover="this.style.background='#E8521A';"
                  onmouseout="this.style.background='#FF6B35';">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          </button>
        </div>
        <button onclick="removeItem(${item.id})"
                class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 flex-shrink-0"
                style="background:#f1f5f9;"
                onmouseover="this.style.background='#fee2e2'; this.style.color='#ef4444';"
                onmouseout="this.style.background='#f1f5f9'; this.style.color='#94a3b8';">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    `).join('');
  }

  // ── Confirm order ─────────────────────────────────────────
  function confirmOrder() {
    const items = Object.values(cart);
    if (items.length === 0) return;

    const btn = document.getElementById('confirm-btn');
    btn.disabled = true;
    btn.textContent = 'Creando pedido…';
    btn.style.background = '#94a3b8';

    fetch(STORE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        table_id:      selectedTableId || null,
        notes:         document.getElementById('order-notes').value.trim() || null,
        customer_name: document.getElementById('order-customer-name').value.trim() || null,
        items:         items.map(i => ({ id: i.id, qty: i.qty })),
      }),
    })
    .then(r => r.json())
    .then(data => {
      if (data.redirect) {
        showToast('✅ ' + (data.message || 'Pedido creado'), 'success');
        setTimeout(() => { window.location.href = data.redirect; }, 900);
      } else {
        showToast(data.message || 'Error al crear pedido', 'error');
        btn.disabled = false;
        btn.textContent = 'Confirmar Pedido';
        btn.style.background = '#FF6B35';
      }
    })
    .catch(() => {
      showToast('Error de conexión', 'error');
      btn.disabled = false;
      btn.textContent = 'Confirmar Pedido';
      btn.style.background = '#FF6B35';
    });
  }

  // ── Table modal ───────────────────────────────────────────
  function openTableModal() {
    const modal = document.getElementById('tableModal');
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function closeTableModal() {
    const modal = document.getElementById('tableModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  function addTable() {
    const input = document.getElementById('new-table-name');
    const name  = input.value.trim();
    if (!name) return;

    fetch(TABLES_STORE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ name }),
    })
    .then(r => r.json())
    .then(data => {
      if (!data.id) { showToast(data.message || 'Error', 'error'); return; }

      input.value = '';

      // Remove "no tables" message if present
      document.getElementById('modal-empty-msg')?.remove();

      // Add to modal list
      const list = document.getElementById('modal-tables-list');
      const div  = document.createElement('div');
      div.id = 'modal-table-' + data.id;
      div.className = 'flex items-center justify-between py-2 px-3 rounded-xl bg-slate-50';
      div.innerHTML = `
        <span class="text-[13px] font-semibold text-slate-700">${data.name}</span>
        <button onclick="deleteTable(${data.id})"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400"
                style="background:#f1f5f9;"
                onmouseover="this.style.background='#fee2e2'; this.style.color='#ef4444';"
                onmouseout="this.style.background='#f1f5f9'; this.style.color='#94a3b8';">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>`;
      list.appendChild(div);

      // Add to table grid
      const grid     = document.getElementById('table-grid');
      const noTables = grid?.querySelector('.py-6');
      noTables?.remove();

      if (grid) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'table-btn relative px-4 py-2.5 rounded-xl border-2 text-[13px] font-bold transition-all cursor-pointer';
        btn.dataset.tableId   = data.id;
        btn.dataset.tableName = data.name;
        btn.style.borderColor = '#e2e8f0';
        btn.style.background  = '#fff';
        btn.style.color       = '#475569';
        btn.textContent       = data.name;
        btn.setAttribute('onmouseover', "if(!this.classList.contains('active-table')) { this.style.borderColor='#FF6B35'; this.style.color='#FF6B35'; }");
        btn.setAttribute('onmouseout',  "if(!this.classList.contains('active-table')) { this.style.borderColor='#e2e8f0'; this.style.color='#475569'; }");
        btn.setAttribute('onclick', `selectTable(${data.id}, this)`);
        grid.appendChild(btn);
      }
    })
    .catch(() => showToast('Error de conexión', 'error'));
  }

  function deleteTable(id) {
    if (!confirm('¿Eliminar esta mesa?')) return;

    fetch(TABLES_BASE_URL + '/' + id, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
    })
    .then(r => r.json())
    .then(data => {
      if (!data.ok) { showToast('Error al eliminar', 'error'); return; }

      document.getElementById('modal-table-' + id)?.remove();

      // Remove from grid
      document.querySelector(`.table-btn[data-table-id="${id}"]`)?.remove();

      // If it was selected, reset to "Sin mesa"
      if (selectedTableId === id) {
        selectedTableId   = null;
        selectedTableName = null;
        const noTableBtn = document.querySelector('.table-btn[data-table-id=""]');
        if (noTableBtn) {
          noTableBtn.classList.add('active-table');
          noTableBtn.style.borderColor = '#FF6B35';
          noTableBtn.style.background  = '#fff5f0';
          noTableBtn.style.color       = '#FF6B35';
        }
        document.getElementById('selected-table-display').classList.add('hidden');
      }
    })
    .catch(() => showToast('Error de conexión', 'error'));
  }
</script>
@endpush
