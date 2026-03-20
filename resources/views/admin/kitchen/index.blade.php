@extends('admin.layouts.app')

@section('title', 'Usuarios Cocina')
@section('topbar-title', 'Usuarios de Cocina')

@section('content')

@php $pendingCount = $pendingCount ?? 0; @endphp

{{-- Mensajes de éxito --}}
@if(session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => showToast(@json(session('success')), 'success'));
</script>
@endif

<div class="max-w-4xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-lg font-bold text-slate-800">Personal de Cocina</h2>
      <p class="text-sm text-slate-500 mt-0.5">Estos usuarios solo pueden ver los pedidos en preparación.</p>
    </div>
    <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold transition-all"
            style="background:#FF6B35;"
            onmouseover="this.style.background='#E8521A'"
            onmouseout="this.style.background='#FF6B35'">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
      </svg>
      Nuevo usuario
    </button>
  </div>

  {{-- Enlace cocina --}}
  <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
    </svg>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-semibold text-amber-800">Pantalla de cocina</p>
      <p class="text-xs text-amber-600 mt-0.5">Los usuarios de cocina ingresan en:
        <a href="{{ url('/kitchen/login') }}" target="_blank" class="underline font-medium">{{ url('/kitchen/login') }}</a>
      </p>
    </div>
  </div>

  {{-- Lista de usuarios --}}
  <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    @if($kitchenUsers->isEmpty())
      <div class="py-16 text-center">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"
             style="background:rgba(255,107,53,0.08);">
          <svg class="w-7 h-7" style="color:#FF6B35;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <p class="text-slate-500 font-medium text-sm">Aún no tienes usuarios de cocina</p>
        <p class="text-slate-400 text-xs mt-1">Crea el primero usando el botón de arriba.</p>
      </div>
    @else
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100">
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Correo</th>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Creado</th>
            <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          @foreach($kitchenUsers as $user)
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#FF6B35,#E8521A);">
                  {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <span class="font-semibold text-slate-700">{{ $user->name }}</span>
              </div>
            </td>
            <td class="px-5 py-3.5 text-slate-500">{{ $user->email }}</td>
            <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $user->created_at->format('d/m/Y') }}</td>
            <td class="px-5 py-3.5 text-right">
              <form method="POST" action="{{ route('admin.kitchen-users.destroy', $user) }}"
                    onsubmit="return confirm('¿Eliminar usuario {{ $user->name }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">
                  Eliminar
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

</div>

{{-- ── Modal crear usuario ── --}}
<div id="modal-create"
     class="hidden fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
      <h3 class="text-base font-bold text-slate-800">Nuevo usuario de cocina</h3>
      <button onclick="document.getElementById('modal-create').classList.add('hidden')"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <form method="POST" action="{{ route('admin.kitchen-users.store') }}" class="px-6 py-5 space-y-4">
      @csrf

      @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600">
          {{ $errors->first() }}
        </div>
      @endif

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nombre</label>
        <input type="text" name="name" value="{{ old('name') }}" required
               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:border-transparent transition"
               style="--tw-ring-color:#FF6B35;"
               placeholder="Ej. Juan Cocinero">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email') }}" required
               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:border-transparent transition"
               placeholder="cocina@restaurante.com">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Contraseña</label>
        <input type="password" name="password" required minlength="6"
               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:border-transparent transition"
               placeholder="Mínimo 6 caracteres">
      </div>

      <div class="flex gap-3 pt-1">
        <button type="button"
                onclick="document.getElementById('modal-create').classList.add('hidden')"
                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
          Cancelar
        </button>
        <button type="submit"
                class="flex-1 px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition-all"
                style="background:#FF6B35;"
                onmouseover="this.style.background='#E8521A'"
                onmouseout="this.style.background='#FF6B35'">
          Crear usuario
        </button>
      </div>
    </form>
  </div>
</div>

@if($errors->any())
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('modal-create').classList.remove('hidden');
  });
</script>
@endif

@endsection
