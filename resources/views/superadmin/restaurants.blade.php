@extends('superadmin.layouts.app')
@section('title', 'Restaurantes — FoodTiendyn Sistema')
@section('topbar-title', 'Restaurantes')

@section('content')

<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Restaurantes</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">{{ $restaurants->total() }} restaurante{{ $restaurants->total() !== 1 ? 's' : '' }} registrado{{ $restaurants->total() !== 1 ? 's' : '' }}</p>
  </div>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('superadmin.restaurants') }}" class="mb-5">
  <div class="relative max-w-sm">
    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar restaurante…"
           class="w-full pl-10 pr-4 py-2.5 text-[13px] bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
  </div>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
  <table class="w-full">
    <thead>
      <tr class="border-b border-slate-100">
        <th class="text-left px-5 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Restaurante</th>
        <th class="text-left px-4 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Propietario</th>
        <th class="text-center px-4 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Pedidos</th>
        <th class="text-right px-4 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Ingresos</th>
        <th class="text-center px-5 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Estado</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-50">
      @forelse($restaurants as $rest)
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-5 py-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center text-base overflow-hidden"
                   style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
                @if($rest->logo_url)
                  <img src="{{ $rest->logo_url }}" class="w-full h-full object-cover">
                @else
                  🏪
                @endif
              </div>
              <div class="min-w-0">
                <p class="text-[13px] font-semibold text-slate-800 truncate">{{ $rest->name }}</p>
                <p class="text-[11px] text-slate-400">Creado {{ \Carbon\Carbon::parse($rest->created_at)->diffForHumans() }}</p>
              </div>
            </div>
          </td>
          <td class="px-4 py-4">
            <p class="text-[13px] text-slate-700 font-medium">{{ $rest->owner?->name ?? '—' }}</p>
            <p class="text-[11px] text-slate-400">{{ $rest->owner?->email }}</p>
          </td>
          <td class="px-4 py-4 text-center">
            <span class="text-[13px] font-bold text-slate-700">{{ $rest->orders_count }}</span>
          </td>
          <td class="px-4 py-4 text-right">
            <span class="text-[13px] font-extrabold" style="color:#6366f1;">
              L. {{ number_format($rest->orders_sum_total ?? 0, 2) }}
            </span>
          </td>
          <td class="px-5 py-4 text-center">
            <form method="POST" action="{{ route('superadmin.restaurants.toggle', $rest->id) }}" class="inline">
              @csrf @method('PATCH')
              <button type="submit"
                      class="text-[10px] font-bold px-3 py-1 rounded-full border transition-all {{ $rest->is_open ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}">
                {{ $rest->is_open ? 'Abierto' : 'Cerrado' }}
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="px-5 py-12 text-center text-[13px] text-slate-400">Sin restaurantes registrados.</td></tr>
      @endforelse
    </tbody>
  </table>

  @if($restaurants->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
      <p class="text-[12px] text-slate-400">Mostrando {{ $restaurants->firstItem() }}–{{ $restaurants->lastItem() }} de {{ $restaurants->total() }}</p>
      <div class="flex gap-1">
        @if($restaurants->onFirstPage())
          <span class="px-3 py-1.5 rounded-lg text-[12px] text-slate-300 border border-slate-100">← Anterior</span>
        @else
          <a href="{{ $restaurants->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-[12px] text-slate-600 border border-slate-200 hover:bg-slate-50">← Anterior</a>
        @endif
        @if($restaurants->hasMorePages())
          <a href="{{ $restaurants->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-[12px] text-slate-600 border border-slate-200 hover:bg-slate-50">Siguiente →</a>
        @else
          <span class="px-3 py-1.5 rounded-lg text-[12px] text-slate-300 border border-slate-100">Siguiente →</span>
        @endif
      </div>
    </div>
  @endif
</div>

@endsection
