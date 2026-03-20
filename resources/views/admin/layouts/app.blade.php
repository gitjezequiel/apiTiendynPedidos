<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'TiendynFood Admin')</title>
  <link rel="icon" type="image/png" href="/logo.png">
  <link rel="apple-touch-icon" href="/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: {
            brand:      '#FF6B35',
            'brand-dark': '#E8521A',
          }
        }
      }
    }
  </script>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; }

    /* Sidebar nav items */
    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 12px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 500;
      color: #94a3b8;
      text-decoration: none;
      transition: background 0.13s, color 0.13s;
      border-left: 3px solid transparent;
      cursor: pointer;
    }
    .nav-item:hover {
      background: rgba(255,107,53,0.1);
      color: #FF6B35;
    }
    .nav-item.active {
      background: rgba(255,107,53,0.14);
      color: #FF6B35;
      border-left-color: #FF6B35;
    }
    .nav-item.disabled {
      opacity: 0.4;
      cursor: not-allowed;
      pointer-events: none;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
    .main-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; }

    /* Stat card hover */
    .stat-card {
      transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.07);
    }

    /* Toast */
    .toast-slide {
      transition: transform 0.3s cubic-bezier(.4,0,.2,1), opacity 0.3s ease;
      transform: translateY(-20px);
    }

    /* Bar chart bars */
    .chart-bar {
      transition: height 0.4s cubic-bezier(.4,0,.2,1);
    }

    /* Line-clamp util */
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">

@php
  $pendingCount = $pendingCount ?? 0;
  $restaurant   = $restaurant ?? null;
@endphp

{{-- ═══════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════ --}}
<aside class="w-[232px] flex-shrink-0 flex flex-col h-full overflow-y-auto" style="background:#0f172a;">

  {{-- Brand --}}
  <div class="px-5 pt-5 pb-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
    <div class="flex items-center gap-3">
      <img src="/logo.png" alt="TiendynFood" class="w-11 h-11 rounded-xl flex-shrink-0" style="box-shadow:0 4px 14px rgba(255,107,53,0.45);">
      <div>
        <div class="text-white font-extrabold text-xl leading-tight tracking-tight">TiendynFood</div>
        <div class="text-[11px] font-medium mt-0.5" style="color:#475569;">Panel Admin</div>
      </div>
    </div>
  </div>

  {{-- Navigation --}}
  <nav class="flex-1 px-3 py-4 space-y-0.5">

    <p class="text-[10px] font-bold uppercase tracking-[0.12em] px-3 mb-2 mt-1" style="color:#334155;">Principal</p>

    <a href="{{ route('admin.dashboard') }}"
       class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      Dashboard
    </a>

    <a href="{{ route('admin.orders') }}"
       class="nav-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      Pedidos
      <span data-pending-badge
            class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full text-white flex-shrink-0 {{ $pendingCount > 0 ? '' : 'hidden' }}"
            style="background:#FF6B35; min-width:20px; text-align:center;">{{ $pendingCount }}</span>
    </a>

    <a href="{{ route('admin.mesas') }}"
       class="nav-item {{ request()->routeIs('admin.mesas') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18"/>
      </svg>
      Mesas
    </a>

    <a href="{{ route('admin.menu') }}"
       class="nav-item {{ request()->routeIs('admin.menu*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
      </svg>
      Menú
    </a>

    <a href="{{ route('admin.customers') }}"
       class="nav-item {{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      Clientes
    </a>

    <a href="{{ route('admin.ratings') }}"
       class="nav-item {{ request()->routeIs('admin.ratings*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
      </svg>
      Reseñas
    </a>

    <a href="{{ route('admin.kitchen-users') }}"
       class="nav-item {{ request()->routeIs('admin.kitchen-users*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      Cocina
    </a>

    <div class="pt-4">
      <p class="text-[10px] font-bold uppercase tracking-[0.12em] px-3 mb-2" style="color:#334155;">Próximamente</p>
      <span class="nav-item disabled">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Finanzas
      </span>
    </div>

  </nav>

  {{-- Restaurant footer --}}
  <div class="px-4 py-4" style="border-top:1px solid rgba(255,255,255,0.06);">
    <div class="flex items-center gap-3 mb-3">
      <div class="w-9 h-9 rounded-lg flex-shrink-0 flex items-center justify-center text-base overflow-hidden"
           style="background:linear-gradient(135deg,#FF6B35,#c94016);">
        @if($restaurant && $restaurant->logo_url)
          <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover">
        @else
          🏪
        @endif
      </div>
      <div class="min-w-0 flex-1">
        <div class="text-white text-[13px] font-semibold truncate leading-tight">
          {{ $restaurant ? $restaurant->name : 'Sin restaurante' }}
        </div>
        @if($restaurant)
          <div class="flex items-center gap-1.5 mt-0.5">
            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $restaurant->is_open ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
            <span class="text-[11px] font-medium {{ $restaurant->is_open ? 'text-emerald-400' : 'text-red-400' }}">
              {{ $restaurant->is_open ? 'Abierto' : 'Cerrado' }}
            </span>
          </div>
        @endif
      </div>
    </div>

    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button type="submit"
              class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-[12px] font-medium transition-colors"
              style="color:#475569;"
              onmouseover="this.style.background='rgba(239,68,68,0.1)'; this.style.color='#f87171';"
              onmouseout="this.style.background=''; this.style.color='#475569';">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Cerrar sesión
      </button>
    </form>
  </div>

</aside>

{{-- ═══════════════════════════════════════════
     MAIN AREA
════════════════════════════════════════════ --}}
<main class="flex-1 flex flex-col overflow-hidden">

  {{-- Topbar --}}
  <header class="bg-white border-b border-slate-200 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
    <div>
      <h1 class="text-[17px] font-bold text-slate-800 leading-tight">@yield('topbar-title', 'Dashboard')</h1>
      <p class="text-[11.5px] text-slate-400 mt-0.5 capitalize">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM YYYY') }}</p>
    </div>

    <div class="flex items-center gap-2.5">

      {{-- Notifications bell --}}
      <a href="{{ route('admin.orders', ['status' => 'pendiente']) }}"
         class="relative w-11 h-11 flex items-center justify-center rounded-xl border transition-colors"
         style="background:#f8fafc; border-color:#e2e8f0;"
         onmouseover="this.style.borderColor='#FF6B35'; this.style.background='#fff5f0';"
         onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc';"
         title="Pedidos pendientes">
        <svg class="w-6 h-6" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span data-pending-badge
              class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1.5 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white {{ $pendingCount > 0 ? '' : 'hidden' }}"
              style="background:#FF6B35;">{{ $pendingCount > 99 ? '99+' : $pendingCount }}</span>
      </a>

      {{-- Divider --}}
      <div class="w-px h-7 bg-slate-200"></div>

      {{-- User --}}
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
             style="background:linear-gradient(135deg,#FF6B35,#E8521A);">
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="hidden md:block leading-tight">
          <div class="text-[13px] font-semibold text-slate-700">{{ auth()->user()->name }}</div>
          <div class="text-[11px] text-slate-400">Administrador</div>
        </div>
      </div>
    </div>
  </header>

  {{-- Page content --}}
  <div class="flex-1 overflow-y-auto p-6 main-scroll">
    @yield('content')
  </div>

</main>

{{-- ═══ PAGE LOADER ═══ --}}
<div id="page-loader" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.45); align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:16px; padding:24px 32px; display:flex; align-items:center; gap:14px; box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <svg style="width:22px;height:22px;animation:spin 0.7s linear infinite;color:#FF6B35;" fill="none" viewBox="0 0 24 24">
      <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="#FF6B35" stroke-width="4"/>
      <path style="opacity:.9" fill="#FF6B35" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
    </svg>
    <span style="font-size:14px;font-weight:600;color:#334155;">Cargando…</span>
  </div>
</div>
<style>
  @keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
  // Show loader on any nav-item click (sidebar links)
  document.querySelectorAll('a.nav-item').forEach(link => {
    link.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (!href || href === '#' || href.startsWith('javascript')) return;
      document.getElementById('page-loader').style.display = 'flex';
    });
  });
  // Hide loader when page finishes loading (back/forward navigation)
  window.addEventListener('pageshow', () => {
    document.getElementById('page-loader').style.display = 'none';
  });
</script>

{{-- ═══ TOAST ═══ --}}
<div id="app-toast"
     class="toast-slide fixed top-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 rounded-xl text-white text-[13px] font-semibold shadow-xl"
     style="display:none; min-width:220px;">
</div>

<script>
  function showToast(msg, type = 'success') {
    const t = document.getElementById('app-toast');
    t.innerHTML = (type === 'success'
      ? '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
      : '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'
    ) + '<span>' + msg + '</span>';
    t.style.background   = type === 'success' ? '#22c55e' : '#ef4444';
    t.style.display      = 'flex';
    t.style.opacity      = '1';
    t.style.transform    = 'translateY(0)';
    clearTimeout(window.__toastTimer);
    window.__toastTimer  = setTimeout(() => {
      t.style.opacity   = '0';
      t.style.transform = 'translateY(12px)';
      setTimeout(() => { t.style.display = 'none'; }, 300);
    }, 3200);
  }
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

  // Query simple: solo filtrar por user_id para evitar índice compuesto
  const q = query(
    collection(db, 'notifications'),
    where('user_id', '==', userId)
  );

  // Ignorar docs que ya existían al cargar la página
  const knownIds = new Set();

  onSnapshot(q, (snapshot) => {
    snapshot.docChanges().forEach((change) => {
      if (change.type !== 'added') return;

      const docId = change.doc.id;
      const data  = change.doc.data();

      // Primera carga: marcar como conocidos y salir
      if (knownIds.size === 0 && snapshot.docChanges().length > 1) {
        snapshot.docs.forEach(d => knownIds.add(d.id));
        return;
      }
      if (knownIds.has(docId)) return;
      knownIds.add(docId);

      // Filtrar solo pedidos nuevos
      if (data.type !== 'new_order') return;

      // Ignorar notificaciones anteriores a cuando se abrió la página
      if (data.created_at && data.created_at < startedAt) return;

      const orderData = data.data || {};
      const name  = orderData.customer_name || (data.message?.split(' de ')[1]) || 'Cliente';
      const total = orderData.total ? parseFloat(orderData.total).toFixed(2) : '0.00';

      // Actualizar badges
      document.querySelectorAll('[data-pending-badge]').forEach(badge => {
        const n = (parseInt(badge.textContent) || 0) + 1;
        badge.textContent = n > 9 ? '9+' : n;
        badge.classList.remove('hidden');
      });

      // Toast
      showToast('🛍️ Nuevo pedido de ' + name + ' — L. ' + total, 'success');

      // Sonido suave
      try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.value = 880; gain.gain.value = 0.1;
        osc.start(); osc.stop(ctx.currentTime + 0.15);
      } catch(e) {}
    });
  }, (err) => {
    console.error('Firebase listener error:', err.message);
  });
</script>

@stack('scripts')
</body>
</html>
