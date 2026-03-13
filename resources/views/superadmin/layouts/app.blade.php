<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'FoodTiendyn Sistema')</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 12px; border-radius: 8px;
      font-size: 13.5px; font-weight: 500; color: #a5b4fc;
      text-decoration: none;
      transition: background 0.13s, color 0.13s;
      border-left: 3px solid transparent;
    }
    .nav-item:hover { background: rgba(99,102,241,0.15); color: #818cf8; }
    .nav-item.active { background: rgba(99,102,241,0.2); color: #a5b4fc; border-left-color: #6366f1; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #312e81; border-radius: 99px; }
    .main-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">

{{-- ── SIDEBAR ── --}}
<aside class="w-[230px] flex-shrink-0 flex flex-col h-full overflow-y-auto" style="background:#0f0e2a;">

  <div class="px-5 pt-5 pb-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
    <div class="flex items-center gap-3">
      <img src="/logo.png" alt="FoodTiendyn" class="w-11 h-11 rounded-xl flex-shrink-0" style="box-shadow:0 4px 14px rgba(99,102,241,0.5);">
      <div>
        <div class="text-white font-extrabold text-xl leading-tight">FoodTiendyn</div>
        <div class="text-[11px] font-semibold mt-0.5 text-indigo-400">Sistema</div>
      </div>
    </div>
  </div>

  <nav class="flex-1 px-3 py-4 space-y-0.5">
    <p class="text-[10px] font-bold uppercase tracking-[0.12em] px-3 mb-2 mt-1 text-indigo-800">Gestión</p>

    <a href="{{ route('superadmin.dashboard') }}"
       class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Dashboard
    </a>

    <a href="{{ route('superadmin.restaurants') }}"
       class="nav-item {{ request()->routeIs('superadmin.restaurants*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      Restaurantes
    </a>

    <a href="{{ route('superadmin.users') }}"
       class="nav-item {{ request()->routeIs('superadmin.users*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Usuarios
    </a>

    <p class="text-[10px] font-bold uppercase tracking-[0.12em] px-3 mb-2 mt-4 text-indigo-800">Configuración</p>

    <a href="{{ route('superadmin.restaurant-categories') }}"
       class="nav-item {{ request()->routeIs('superadmin.restaurant-categories*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
      Categorías
    </a>

    <a href="{{ route('superadmin.payment-methods') }}"
       class="nav-item {{ request()->routeIs('superadmin.payment-methods*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
      Medios de pago
    </a>

    <a href="{{ route('superadmin.announcements') }}"
       class="nav-item {{ request()->routeIs('superadmin.announcements*') ? 'active' : '' }}">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
      Anuncios
    </a>
  </nav>

  <div class="px-4 py-4" style="border-top:1px solid rgba(255,255,255,0.06);">
    <div class="flex items-center gap-3 mb-3">
      <div class="w-9 h-9 rounded-lg flex-shrink-0 flex items-center justify-center text-white text-sm font-bold"
           style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
      </div>
      <div class="min-w-0 flex-1">
        <div class="text-white text-[13px] font-semibold truncate leading-tight">{{ auth()->user()->name }}</div>
        <div class="text-indigo-400 text-[11px] font-medium mt-0.5">Super Admin</div>
      </div>
    </div>
    <form method="POST" action="{{ route('superadmin.logout') }}">
      @csrf
      <button type="submit"
              class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-[12px] font-medium text-indigo-400 transition-colors hover:bg-red-900/30 hover:text-red-400">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Cerrar sesión
      </button>
    </form>
  </div>

</aside>

{{-- ── MAIN ── --}}
<main class="flex-1 flex flex-col overflow-hidden">
  <header class="bg-white border-b border-slate-200 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
    <div>
      <h1 class="text-[17px] font-bold text-slate-800 leading-tight">@yield('topbar-title', 'Dashboard')</h1>
      <p class="text-[11.5px] text-slate-400 mt-0.5 capitalize">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM YYYY') }}</p>
    </div>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold text-white" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
      Sistema
    </span>
  </header>

  <div class="flex-1 overflow-y-auto p-6 main-scroll">
    @if(session('success'))
      <div class="flex items-center gap-2.5 mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-[13px] font-medium">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
      </div>
    @endif
    @yield('content')
  </div>
</main>

{{-- Page loader --}}
<div id="page-loader" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,14,42,0.5); align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:16px; padding:24px 32px; display:flex; align-items:center; gap:14px; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
    <svg style="width:22px;height:22px;animation:spin 0.7s linear infinite;" fill="none" viewBox="0 0 24 24">
      <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="#6366f1" stroke-width="4"/>
      <path style="opacity:.9" fill="#6366f1" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
    </svg>
    <span style="font-size:14px;font-weight:600;color:#334155;">Cargando…</span>
  </div>
</div>
<script>
  document.querySelectorAll('a.nav-item').forEach(link => {
    link.addEventListener('click', function() {
      const href = this.getAttribute('href');
      if (!href || href === '#') return;
      document.getElementById('page-loader').style.display = 'flex';
    });
  });
  window.addEventListener('pageshow', () => {
    document.getElementById('page-loader').style.display = 'none';
  });
</script>

@stack('scripts')
</body>
</html>
