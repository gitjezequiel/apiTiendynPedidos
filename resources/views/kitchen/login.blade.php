<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cocina — TiendynFood</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background:#0f172a;">

<div class="w-full max-w-sm mx-4">

  {{-- Logo --}}
  <div class="text-center mb-8">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
         style="background:linear-gradient(135deg,#FF6B35,#E8521A); box-shadow:0 8px 32px rgba(255,107,53,0.4);">
      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
    </div>
    <h1 class="text-2xl font-extrabold text-white tracking-tight">Panel de Cocina</h1>
    <p class="text-slate-400 text-sm mt-1">Ingresa con tu usuario de cocina</p>
  </div>

  {{-- Card --}}
  <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
    <div class="px-7 py-8">

      @if($errors->any())
        <div class="mb-4 flex items-center gap-2.5 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
          <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="text-sm text-red-600 font-medium">{{ $errors->first() }}</p>
        </div>
      @endif

      <form method="POST" action="{{ route('kitchen.login.post') }}" id="login-form">
        @csrf

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Usuario</label>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                   class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:border-transparent transition"
                   style="--tw-ring-color:#FF6B35;"
                   placeholder="mi_usuario">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Contraseña</label>
            <input type="password" name="password" required
                   class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:border-transparent transition"
                   placeholder="••••••••">
          </div>
        </div>

        <button type="submit" id="submit-btn"
                class="w-full mt-6 py-3 rounded-xl text-white font-bold text-sm transition-all flex items-center justify-center gap-2"
                style="background:linear-gradient(135deg,#FF6B35,#E8521A); box-shadow:0 4px 18px rgba(255,107,53,0.35);">
          <span id="btn-text">Ingresar</span>
          <svg id="btn-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/>
            <path style="opacity:.9" fill="white" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
        </button>
      </form>
    </div>
  </div>

</div>

<script>
  document.getElementById('login-form').addEventListener('submit', function() {
    document.getElementById('btn-text').textContent = 'Ingresando…';
    document.getElementById('btn-spinner').classList.remove('hidden');
    document.getElementById('submit-btn').disabled = true;
  });
</script>
</body>
</html>
