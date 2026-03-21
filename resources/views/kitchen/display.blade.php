<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Cocina — TiendynFood</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }

    @keyframes ping-slow {
      0%, 100% { transform: scale(1); opacity: 1; }
      50%       { transform: scale(1.2); opacity: 0.6; }
    }
    .badge-ping { animation: ping-slow 1.8s ease-in-out infinite; }

    @keyframes slide-up {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .slide-up { animation: slide-up 0.3s ease both; }

    @keyframes flash-new {
      0%   { background: rgba(255,107,53,0.18); }
      50%  { background: rgba(255,107,53,0.06); }
      100% { background: rgba(255,107,53,0.18); }
    }
    .new-flash { animation: flash-new 0.6s ease 3; }

    @keyframes screen-flash {
      0%,100% { opacity: 0; }
      20%,60% { opacity: 1; }
    }

    /* Scrollbar minimal */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }

    /* Layout fijo de pantalla completa */
    html, body { height: 100%; overflow: hidden; }
    #layout { display: flex; flex-direction: column; height: 100vh; }
    #body    { display: flex; flex: 1; overflow: hidden; }
    #queue-panel   { width: 300px; flex-shrink: 0; overflow-y: auto; border-right: 1px solid rgba(255,255,255,0.06); }
    #current-panel { flex: 1; overflow-y: auto; display: flex; align-items: center; justify-content: center; padding: 2rem; }

    /* Botón listo */
    #listo-btn {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      box-shadow: 0 6px 28px rgba(34,197,94,0.4);
      transition: transform 0.1s, box-shadow 0.1s, opacity 0.2s;
    }
    #listo-btn:active { transform: scale(0.97); box-shadow: 0 2px 12px rgba(34,197,94,0.3); }
    #listo-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Toast notificación */
    #toast {
      position: fixed; top: 72px; left: 50%; transform: translateX(-50%) translateY(-8px);
      opacity: 0; pointer-events: none; transition: opacity 0.25s, transform 0.25s;
      z-index: 100;
    }
    #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

    /* Overlay flash pantalla */
    #screen-overlay {
      position: fixed; inset: 0; pointer-events: none; z-index: 50;
      background: rgba(255,107,53,0.12); opacity: 0;
    }
  </style>
</head>
<body style="background:#0f172a;">

<div id="layout">

{{-- ══ TOPBAR ══ --}}
<header class="flex items-center justify-between px-6 py-3 flex-shrink-0 border-b"
        style="background:#0f172a; border-color:rgba(255,255,255,0.06);">
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
    {{-- Contador --}}
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl" style="background:rgba(255,107,53,0.12);">
      <span id="order-count" class="badge-ping text-lg font-black" style="color:#FF6B35;">{{ $orders->count() }}</span>
      <span class="text-slate-400 text-xs font-medium">en preparación</span>
    </div>

    {{-- Reloj --}}
    <div class="text-right">
      <div id="clock" class="text-white font-bold text-lg tabular-nums"></div>
      <div id="date-display" class="text-slate-500 text-xs capitalize"></div>
    </div>

    {{-- Estado conexión --}}
    <div class="flex items-center gap-1.5">
      <span id="conn-dot" class="w-2 h-2 rounded-full bg-emerald-400"></span>
      <span id="conn-text" class="text-slate-500 text-xs">En vivo</span>
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

{{-- ══ BODY ══ --}}
<div id="body">

  {{-- ── COLA DE PEDIDOS (izquierda) ─────────────────────────── --}}
  <aside id="queue-panel" class="p-4 space-y-3">
    <div class="flex items-center justify-between mb-1 px-1">
      <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">En cola</p>
      <span id="queue-count" class="text-xs font-black px-2 py-0.5 rounded-lg"
            style="background:rgba(255,107,53,0.15); color:#FF6B35;">0</span>
    </div>
    <div id="queue-list" class="space-y-3"></div>
    <div id="queue-empty" class="hidden py-12 text-center">
      <p class="text-slate-600 text-sm">Sin pedidos en espera</p>
    </div>
  </aside>

  {{-- ── PEDIDO ACTUAL (derecha) ─────────────────────────────── --}}
  <section id="current-panel">

    {{-- Estado vacío global --}}
    <div id="empty-state" class="hidden text-center">
      <div class="w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-6"
           style="background:rgba(255,107,53,0.07);">
        <svg class="w-12 h-12" style="color:#FF6B35;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <p class="text-slate-300 font-bold text-2xl">Sin pedidos activos</p>
      <p class="text-slate-500 text-sm mt-2">Cuando el dueño apruebe un pedido, aparecerá aquí.</p>
    </div>

    {{-- Tarjeta del pedido actual --}}
    <div id="current-card" class="hidden w-full" style="max-width: 580px;">
      <div class="rounded-3xl overflow-hidden border slide-up"
           style="background:#1e293b; border-color:rgba(255,255,255,0.08); box-shadow: 0 24px 80px rgba(0,0,0,0.5);">

        {{-- Header grande --}}
        <div class="px-8 py-6 border-b" style="border-color:rgba(255,255,255,0.07); background:rgba(255,255,255,0.02);">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Preparando ahora</p>
              <div id="cur-number"
                   class="inline-block px-4 py-1.5 rounded-xl text-white text-2xl font-black tracking-tight"
                   style="background:linear-gradient(135deg,#FF6B35,#E8521A); box-shadow:0 4px 20px rgba(255,107,53,0.4);">
              </div>
            </div>
            <div class="text-right flex-shrink-0">
              <div id="cur-location" class="mb-2"></div>
              <div class="flex items-center justify-end gap-1.5">
                <div class="w-2 h-2 rounded-full bg-amber-400 badge-ping"></div>
                <span id="cur-time" class="text-amber-400 text-sm font-semibold"></span>
              </div>
            </div>
          </div>
        </div>

        {{-- Cliente --}}
        <div id="cur-customer-row" class="px-8 pt-5 pb-1 hidden">
          <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider mb-1">Cliente</p>
          <p id="cur-customer" class="text-slate-200 text-base font-bold"></p>
        </div>

        {{-- Items --}}
        <div class="px-8 py-5">
          <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider mb-4">Productos</p>
          <div id="cur-items" class="space-y-4"></div>
        </div>

        {{-- Notas --}}
        <div id="cur-notes-row" class="mx-8 mb-5 px-4 py-3 rounded-2xl border border-amber-500/20 hidden"
             style="background:rgba(245,158,11,0.07);">
          <p class="text-amber-400 text-xs font-bold uppercase tracking-wider mb-1">Nota especial</p>
          <p id="cur-notes" class="text-amber-300 text-sm font-medium"></p>
        </div>

        {{-- Botón LISTO --}}
        <div class="px-8 pb-8">
          <button id="listo-btn" onclick="markListo()"
                  class="w-full py-5 rounded-2xl text-white text-xl font-black flex items-center justify-center gap-3">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span id="listo-text">Pedido listo</span>
          </button>
        </div>

      </div>
    </div>

  </section>
</div>
</div>

{{-- Toast notificación --}}
<div id="toast">
  <div class="flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl"
       style="background:#1e293b; border:1px solid rgba(255,107,53,0.3); box-shadow:0 8px 32px rgba(0,0,0,0.5);">
    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
         style="background:rgba(255,107,53,0.15);">
      <svg class="w-4 h-4" style="color:#FF6B35;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
    </div>
    <div>
      <p class="text-white text-sm font-bold">Nuevo pedido</p>
      <p id="toast-msg" class="text-slate-400 text-xs"></p>
    </div>
  </div>
</div>

{{-- Overlay flash --}}
<div id="screen-overlay"></div>

<script>
  // ── Reloj ───────────────────────────────────────────────────────
  function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent =
      now.toLocaleTimeString('es-HN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    document.getElementById('date-display').textContent =
      now.toLocaleDateString('es-HN', { weekday: 'long', day: 'numeric', month: 'short' });
  }
  updateClock();
  setInterval(updateClock, 1000);

  // ── Tiempo relativo ─────────────────────────────────────────────
  function timeAgo(isoString) {
    const sec = Math.floor((Date.now() - new Date(isoString)) / 1000);
    if (sec < 60)  return sec + ' seg';
    const min = Math.floor(sec / 60);
    if (min < 60)  return min + ' min';
    return Math.floor(min / 60) + ' h';
  }

  // ── Sonido ──────────────────────────────────────────────────────
  function playBeep(freq = 880, duration = 0.35, volume = 0.18) {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain); gain.connect(ctx.destination);
      osc.frequency.value = freq;
      gain.gain.setValueAtTime(volume, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
      osc.start(); osc.stop(ctx.currentTime + duration);
      // doble beep para notificación
      const osc2 = ctx.createOscillator();
      const gain2 = ctx.createGain();
      osc2.connect(gain2); gain2.connect(ctx.destination);
      osc2.frequency.value = freq * 1.25;
      gain2.gain.setValueAtTime(volume, ctx.currentTime + duration + 0.08);
      gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration * 2 + 0.08);
      osc2.start(ctx.currentTime + duration + 0.08);
      osc2.stop(ctx.currentTime + duration * 2 + 0.08);
    } catch(e) {}
  }

  // ── Toast ───────────────────────────────────────────────────────
  let toastTimer;
  function showToast(msg) {
    const el = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 4000);
  }

  // ── Flash pantalla ──────────────────────────────────────────────
  function flashScreen() {
    const ov = document.getElementById('screen-overlay');
    ov.style.transition = 'none';
    ov.style.opacity = '1';
    setTimeout(() => {
      ov.style.transition = 'opacity 0.6s';
      ov.style.opacity = '0';
    }, 120);
  }

  // ── Estado global ───────────────────────────────────────────────
  let orders = [];
  let knownIds = new Set();
  let currentOrderId = null;

  // ── Render cola (sidebar) ───────────────────────────────────────
  function renderQueue(queue) {
    const list = document.getElementById('queue-list');
    const empty = document.getElementById('queue-empty');
    const countEl = document.getElementById('queue-count');

    countEl.textContent = queue.length;

    if (queue.length === 0) {
      list.innerHTML = '';
      empty.classList.remove('hidden');
      return;
    }
    empty.classList.add('hidden');

    const incomingIds = new Set(queue.map(o => o.id));

    // Eliminar las que ya no están
    list.querySelectorAll('[data-qid]').forEach(el => {
      if (!incomingIds.has(Number(el.dataset.qid))) {
        el.style.opacity = '0';
        el.style.transition = 'opacity 0.25s';
        setTimeout(() => el.remove(), 250);
      }
    });

    // Agregar las nuevas
    queue.forEach((order, i) => {
      if (!list.querySelector(`[data-qid="${order.id}"]`)) {
        const div = document.createElement('div');
        div.dataset.qid = order.id;
        div.className = 'rounded-2xl overflow-hidden border slide-up';
        div.style.cssText = 'background:#1e293b; border-color:rgba(255,255,255,0.07);';
        div.innerHTML = buildQueueCard(order, i);
        list.appendChild(div);
      }
    });
  }

  function buildQueueCard(order, i) {
    const loc = order.table
      ? `<span style="color:#a5b4fc;">Mesa ${order.table.number}</span>`
      : `<span class="text-slate-500">${order.delivery_mode === 'delivery' ? '🛵 Delivery' : '🥡 Para llevar'}</span>`;

    const items = order.items.map(it =>
      `<span class="text-slate-400 text-xs">${it.quantity}× ${it.name}</span>`
    ).join('<br>');

    return `
      <div class="px-4 py-3">
        <div class="flex items-center justify-between mb-2">
          <div class="px-2 py-0.5 rounded-lg text-white text-xs font-black"
               style="background:linear-gradient(135deg,#FF6B35,#E8521A);">${order.order_number}</div>
          <span class="text-amber-400 text-xs font-semibold order-time" data-time="${order.updated_at}">${timeAgo(order.updated_at)}</span>
        </div>
        <div class="text-xs mb-1.5">${loc}</div>
        <div class="leading-5">${items}</div>
        ${order.customer_name ? `<p class="text-slate-600 text-xs mt-1.5">👤 ${order.customer_name}</p>` : ''}
      </div>`;
  }

  // ── Render pedido actual (main) ─────────────────────────────────
  function renderCurrent(order) {
    if (!order) {
      document.getElementById('current-card').classList.add('hidden');
      document.getElementById('empty-state').classList.remove('hidden');
      document.getElementById('empty-state').classList.add('flex');
      currentOrderId = null;
      return;
    }

    document.getElementById('empty-state').classList.add('hidden');
    document.getElementById('empty-state').classList.remove('flex');

    const card = document.getElementById('current-card');

    // Si cambió de pedido, re-animar
    if (currentOrderId !== order.id) {
      card.querySelector('.rounded-3xl').classList.remove('slide-up');
      void card.querySelector('.rounded-3xl').offsetWidth; // reflow
      card.querySelector('.rounded-3xl').classList.add('slide-up');
      currentOrderId = order.id;
    }

    card.classList.remove('hidden');

    document.getElementById('cur-number').textContent = order.order_number;

    // Ubicación
    const locEl = document.getElementById('cur-location');
    if (order.table) {
      locEl.innerHTML = `
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-bold"
             style="background:rgba(99,102,241,0.2); color:#a5b4fc;">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18"/>
          </svg>
          Mesa ${order.table.number}
        </div>`;
    } else if (order.delivery_mode === 'delivery') {
      locEl.innerHTML = `<span class="text-2xl">🛵</span><span class="text-slate-300 text-sm font-bold ml-1">Delivery</span>`;
    } else {
      locEl.innerHTML = `<span class="text-2xl">🥡</span><span class="text-slate-300 text-sm font-bold ml-1">Para llevar</span>`;
    }

    // Tiempo
    const timeEl = document.getElementById('cur-time');
    timeEl.dataset.time = order.updated_at;
    timeEl.textContent = timeAgo(order.updated_at);

    // Cliente
    const custRow = document.getElementById('cur-customer-row');
    if (order.customer_name) {
      document.getElementById('cur-customer').textContent = order.customer_name;
      custRow.classList.remove('hidden');
    } else {
      custRow.classList.add('hidden');
    }

    // Items
    document.getElementById('cur-items').innerHTML = order.items.map(item => `
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-xl flex-shrink-0"
             style="background:rgba(255,107,53,0.15); color:#FF6B35;">${item.quantity}</div>
        <div>
          <p class="text-white text-lg font-bold leading-tight">${item.name}</p>
          ${item.notes ? `<p class="text-amber-400 text-sm mt-0.5 font-medium">⚠ ${item.notes}</p>` : ''}
        </div>
      </div>`).join('');

    // Notas
    const notesRow = document.getElementById('cur-notes-row');
    if (order.notes) {
      document.getElementById('cur-notes').textContent = order.notes;
      notesRow.classList.remove('hidden');
    } else {
      notesRow.classList.add('hidden');
    }

    // Resetear botón
    const btn = document.getElementById('listo-btn');
    btn.disabled = false;
    document.getElementById('listo-text').textContent = 'Pedido listo';
  }

  // ── Render principal ────────────────────────────────────────────
  function renderAll(newOrders) {
    const countEl = document.getElementById('order-count');
    countEl.textContent = newOrders.length;

    const current = newOrders[0] || null;
    const queue   = newOrders.slice(1);

    renderCurrent(current);
    renderQueue(queue);
  }

  // ── Marcar listo ────────────────────────────────────────────────
  async function markListo() {
    if (!currentOrderId) return;
    const btn = document.getElementById('listo-btn');
    btn.disabled = true;
    document.getElementById('listo-text').textContent = 'Marcando…';

    try {
      const res = await fetch(`/kitchen/orders/${currentOrderId}/listo`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      if (!res.ok) throw new Error();

      // Efecto visual de éxito antes de remover
      btn.style.background = 'linear-gradient(135deg,#4ade80,#22c55e)';
      document.getElementById('listo-text').textContent = '¡Listo!';
      playBeep(660, 0.2, 0.12);

      setTimeout(() => {
        orders = orders.filter(o => o.id !== currentOrderId);
        renderAll(orders);
      }, 500);

    } catch(e) {
      btn.disabled = false;
      document.getElementById('listo-text').textContent = 'Pedido listo';
      btn.style.background = '';
    }
  }

  // ── Fetch órdenes desde servidor ────────────────────────────────
  const POLL_URL = '{{ route("kitchen.orders.json") }}';
  let failCount = 0;

  window.fetchOrders = async function fetchOrders(notifyNew = false) {
    try {
      const res = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error(res.status);
      const data = await res.json();
      const newOrders = data.orders;

      if (notifyNew) {
        newOrders.forEach(o => {
          if (!knownIds.has(o.id)) {
            playBeep(880, 0.35, 0.18);
            flashScreen();
            showToast(`${o.order_number}${o.customer_name ? ' — ' + o.customer_name : ''}`);
          }
        });
      }

      knownIds = new Set(newOrders.map(o => o.id));
      orders = newOrders;
      renderAll(orders);

      failCount = 0;
      document.getElementById('conn-dot').style.background = '#34d399';
      document.getElementById('conn-text').textContent = 'En vivo';
    } catch(e) {
      failCount++;
      document.getElementById('conn-dot').style.background = '#f87171';
      document.getElementById('conn-text').textContent = 'Sin conexión';
      if (failCount >= 5) location.reload();
    }
  }

  // ── Carga inicial ───────────────────────────────────────────────
  orders = @json($ordersJson);
  knownIds = new Set(orders.map(o => o.id));
  renderAll(orders);

  // Actualizar tiempos cada 30 s
  setInterval(() => {
    document.querySelectorAll('.order-time').forEach(el => {
      el.textContent = timeAgo(el.dataset.time);
    });
    const curTime = document.getElementById('cur-time');
    if (curTime && curTime.dataset.time) {
      curTime.textContent = timeAgo(curTime.dataset.time);
    }
  }, 30000);

</script>

{{-- ═══ FIREBASE REAL-TIME ═══ --}}
<script type="module">
  import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
  import { getFirestore, collection, query, where, onSnapshot }
    from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js';

  const app = initializeApp({
    apiKey:            'AIzaSyAnA0rBNZLzBoXtCXlZl_Y0v0zpvOFdCWA',
    authDomain:        'foodtiendyn.firebaseapp.com',
    projectId:         'foodtiendyn',
    storageBucket:     'foodtiendyn.firebasestorage.app',
    messagingSenderId: '977334041017',
    appId:             '1:977334041017:web:2fc5ed380b59e9df085e68',
  });

  const db        = getFirestore(app);
  const userId    = '{{ auth()->id() }}';
  const startedAt = Date.now();
  const seenIds   = new Set();

  const q = query(
    collection(db, 'notifications'),
    where('user_id', '==', userId)
  );

  onSnapshot(q, (snapshot) => {
    // Primera carga: marcar todos como conocidos para no disparar notificaciones viejas
    if (seenIds.size === 0) {
      snapshot.docs.forEach(d => seenIds.add(d.id));
      document.getElementById('conn-dot').style.background = '#34d399';
      document.getElementById('conn-text').textContent = 'Firebase';
      return;
    }

    snapshot.docChanges().forEach((change) => {
      if (change.type !== 'added') return;
      if (seenIds.has(change.doc.id)) return;
      seenIds.add(change.doc.id);

      const data = change.doc.data();
      if (data.type !== 'new_order_kitchen') return;
      if (data.created_at && data.created_at < startedAt) return;

      // Nuevo pedido llegó a cocina → actualizar lista
      window.fetchOrders(true);
    });
  }, (err) => {
    console.error('Firebase error:', err.message);
    document.getElementById('conn-dot').style.background = '#f59e0b';
    document.getElementById('conn-text').textContent = 'Sin Firebase';
  });
</script>

</body>
</html>
