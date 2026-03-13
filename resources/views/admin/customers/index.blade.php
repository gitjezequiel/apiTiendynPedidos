@extends('admin.layouts.app')

@section('title', 'Clientes — TiendynFood Admin')
@section('topbar-title', 'Clientes')

@section('content')

{{-- ── Page header ── --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Clientes</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">Personas que han realizado pedidos en tu restaurante</p>
  </div>
  @if($restaurant)
    <div class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl shadow-sm">
      <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      <span class="text-[13px] font-bold text-slate-800">{{ $customers->total() }}</span>
      <span class="text-[12px] text-slate-400">cliente{{ $customers->total() !== 1 ? 's' : '' }}</span>
    </div>
  @endif
</div>

@if(!$restaurant)
  <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-base">⚠️</div>
    <div>
      <p class="text-sm font-semibold text-amber-800">Sin restaurante registrado</p>
      <p class="text-[13px] text-amber-700 mt-0.5">No tienes un restaurante en el sistema.</p>
    </div>
  </div>
@else

  {{-- Search --}}
  <form method="GET" action="{{ route('admin.customers') }}" class="mb-5">
    <div class="relative max-w-sm">
      <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" name="search" value="{{ request('search') }}"
             placeholder="Buscar por nombre o email…"
             class="w-full pl-10 pr-4 py-2.5 text-[13px] bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all">
    </div>
  </form>

  @if($customers->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
      <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-4">👥</div>
      <p class="text-[15px] font-bold text-slate-700 mb-1">Sin clientes todavía</p>
      <p class="text-[13px] text-slate-400">Cuando alguien haga un pedido aparecerá aquí.</p>
    </div>
  @else

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <table class="w-full">
        <thead>
          <tr class="border-b border-slate-100">
            <th class="text-left px-5 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Cliente</th>
            <th class="text-center px-4 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Pedidos</th>
            <th class="text-right px-4 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Total gastado</th>
            <th class="text-right px-5 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wide">Último pedido</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          @foreach($customers as $customer)
            <tr class="hover:bg-slate-50 transition-colors">
              {{-- Avatar + info --}}
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                       style="background:linear-gradient(135deg,#FF6B35,#E8521A);">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                  </div>
                  <div class="min-w-0">
                    <p class="text-[13px] font-semibold text-slate-800 truncate">{{ $customer->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $customer->email }}</p>
                  </div>
                </div>
              </td>
              {{-- Orders count --}}
              <td class="px-4 py-4 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-orange-50 text-orange-600">
                  {{ $customer->total_orders }}
                </span>
              </td>
              {{-- Total spent --}}
              <td class="px-4 py-4 text-right">
                <span class="text-[14px] font-extrabold" style="color:#FF6B35;">
                  L. {{ number_format($customer->total_spent ?? 0, 2) }}
                </span>
              </td>
              {{-- Last order --}}
              <td class="px-5 py-4 text-right">
                <span class="text-[12px] text-slate-500">
                  {{ $customer->last_order_at ? \Carbon\Carbon::parse($customer->last_order_at)->diffForHumans() : '—' }}
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Pagination --}}
      @if($customers->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
          <p class="text-[12px] text-slate-400">
            Mostrando {{ $customers->firstItem() }}–{{ $customers->lastItem() }} de {{ $customers->total() }}
          </p>
          <div class="flex gap-1">
            @if($customers->onFirstPage())
              <span class="px-3 py-1.5 rounded-lg text-[12px] text-slate-300 border border-slate-100">← Anterior</span>
            @else
              <a href="{{ $customers->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-[12px] text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">← Anterior</a>
            @endif
            @if($customers->hasMorePages())
              <a href="{{ $customers->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-[12px] text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">Siguiente →</a>
            @else
              <span class="px-3 py-1.5 rounded-lg text-[12px] text-slate-300 border border-slate-100">Siguiente →</span>
            @endif
          </div>
        </div>
      @endif
    </div>

  @endif
@endif

@endsection
