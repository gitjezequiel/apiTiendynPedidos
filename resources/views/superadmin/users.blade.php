@extends('superadmin.layouts.app')
@section('title', 'Usuarios — FoodTiendyn Sistema')
@section('topbar-title', 'Usuarios')

@section('content')

<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Usuarios</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">{{ $users->total() }} usuario{{ $users->total() !== 1 ? 's' : '' }}</p>
  </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('superadmin.users') }}" class="flex items-center gap-3 mb-5 flex-wrap">
  <div class="relative">
    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o email…"
           class="pl-10 pr-4 py-2.5 text-[13px] bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all w-72">
  </div>
  <div class="flex gap-1 bg-white border border-slate-200 rounded-xl p-1">
    @foreach(['' => 'Todos', 'owner' => 'Propietarios', 'customer' => 'Clientes'] as $val => $label)
      <a href="{{ route('superadmin.users', array_merge(request()->query(), ['role' => $val])) }}"
         class="px-3.5 py-1.5 rounded-lg text-[12px] font-semibold transition-all {{ request('role', '') === $val ? 'text-white' : 'text-slate-500 hover:text-slate-700' }}"
         style="{{ request('role', '') === $val ? 'background:linear-gradient(135deg,#6366f1,#4f46e5);' : '' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
  <table class="w-full">
    <thead>
      <tr class="border-b border-slate-100">
        <th class="text-left px-5 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Usuario</th>
        <th class="text-center px-4 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Rol</th>
        <th class="text-left px-4 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Teléfono</th>
        <th class="text-right px-5 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Registrado</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-50">
      @forelse($users as $user)
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-5 py-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center text-white text-sm font-bold overflow-hidden"
                   style="background:linear-gradient(135deg,{{ $user->role === 'owner' ? '#8b5cf6,#6d28d9' : '#06b6d4,#0891b2' }});">
                @if($user->profile_image)
                  <img src="{{ $user->profile_image }}" class="w-full h-full object-cover">
                @else
                  {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
              </div>
              <div class="min-w-0">
                <p class="text-[13px] font-semibold text-slate-800 truncate">{{ $user->name }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ $user->email }}</p>
              </div>
            </div>
          </td>
          <td class="px-4 py-4 text-center">
            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $user->role === 'owner' ? 'bg-purple-50 text-purple-700' : 'bg-cyan-50 text-cyan-700' }}">
              {{ $user->role === 'owner' ? 'Propietario' : 'Cliente' }}
            </span>
          </td>
          <td class="px-4 py-4">
            <span class="text-[13px] text-slate-600">{{ $user->phone ?: '—' }}</span>
          </td>
          <td class="px-5 py-4 text-right">
            <span class="text-[12px] text-slate-500">{{ \Carbon\Carbon::parse($user->created_at)->diffForHumans() }}</span>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-5 py-12 text-center text-[13px] text-slate-400">Sin usuarios.</td></tr>
      @endforelse
    </tbody>
  </table>

  @if($users->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
      <p class="text-[12px] text-slate-400">Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }}</p>
      <div class="flex gap-1">
        @if($users->onFirstPage())
          <span class="px-3 py-1.5 rounded-lg text-[12px] text-slate-300 border border-slate-100">← Anterior</span>
        @else
          <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-[12px] text-slate-600 border border-slate-200 hover:bg-slate-50">← Anterior</a>
        @endif
        @if($users->hasMorePages())
          <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-[12px] text-slate-600 border border-slate-200 hover:bg-slate-50">Siguiente →</a>
        @else
          <span class="px-3 py-1.5 rounded-lg text-[12px] text-slate-300 border border-slate-100">Siguiente →</span>
        @endif
      </div>
    </div>
  @endif
</div>

@endsection
