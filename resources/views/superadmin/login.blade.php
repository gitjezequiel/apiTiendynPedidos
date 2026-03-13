<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodTiendyn — Sistema</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%);">

  <div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4 shadow-2xl overflow-hidden" style="box-shadow:0 8px 32px rgba(99,102,241,0.4);">
        <img src="/logo.png" alt="FoodTiendyn" class="w-full h-full object-cover">
      </div>
      <h1 class="text-white text-2xl font-extrabold tracking-tight">FoodTiendyn</h1>
      <p class="text-indigo-300 text-[13px] mt-1 font-medium">Panel de Sistema</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl p-8">
      <h2 class="text-[17px] font-bold text-slate-800 mb-1">Acceso de Administrador</h2>
      <p class="text-[13px] text-slate-400 mb-6">Solo para el equipo FoodTiendyn</p>

      @if($errors->any())
        <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5 text-[13px] text-red-600 font-medium">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/></svg>
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('superadmin.login.post') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus
                 class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                 placeholder="admin@foodtiendyn.com">
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Contraseña</label>
          <input type="password" name="password" autocomplete="current-password"
                 class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                 placeholder="••••••••">
        </div>
        <button id="loginBtn" type="submit"
                class="w-full py-3 rounded-xl text-[14px] font-bold text-white transition-opacity hover:opacity-90 flex items-center justify-center gap-2"
                style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
          <svg id="loginSpinner" class="hidden w-4 h-4" style="animation:spin .7s linear infinite;" fill="none" viewBox="0 0 24 24">
            <circle style="opacity:.3" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/>
            <path style="opacity:.9" fill="white" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <span id="loginBtnText">Ingresar al sistema</span>
        </button>
      </form>
      <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
      <script>
        document.querySelector('form').addEventListener('submit', function() {
          document.getElementById('loginSpinner').classList.remove('hidden');
          document.getElementById('loginBtnText').textContent = 'Ingresando…';
          document.getElementById('loginBtn').disabled = true;
          document.getElementById('loginBtn').style.opacity = '0.8';
        });
      </script>
    </div>

    <p class="text-center text-indigo-400 text-[12px] mt-6">FoodTiendyn © {{ date('Y') }}</p>
  </div>

</body>
</html>
