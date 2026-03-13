@extends('admin.layouts.app')

@section('title', 'Reseñas — TiendynFood Admin')
@section('topbar-title', 'Reseñas')

@section('content')

@php
  $stars = fn(int $n) => str_repeat('★', $n) . str_repeat('☆', 5 - $n);
  $scoreColor = fn(float $s) => $s >= 4 ? 'text-emerald-600 bg-emerald-50' : ($s >= 3 ? 'text-amber-600 bg-amber-50' : 'text-red-600 bg-red-50');
@endphp

{{-- ── Page header ── --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Reseñas</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">Calificaciones de los clientes sobre tu restaurante</p>
  </div>
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

  {{-- ── Summary cards ── --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Average score --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
           style="background:linear-gradient(135deg,#fff3ee,#ffe4d6);">⭐</div>
      <div>
        <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wide">Promedio</p>
        <p class="text-[28px] font-extrabold leading-tight" style="color:#FF6B35;">{{ number_format($avgScore, 1) }}</p>
        <p class="text-[11px] text-slate-400">de 5.0</p>
      </div>
    </div>

    {{-- Total ratings --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
           style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">💬</div>
      <div>
        <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wide">Total reseñas</p>
        <p class="text-[28px] font-extrabold text-slate-800 leading-tight">{{ $totalRatings }}</p>
        <p class="text-[11px] text-slate-400">calificaciones</p>
      </div>
    </div>

    {{-- Distribution --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wide mb-3">Distribución</p>
      <div class="flex flex-col gap-1.5">
        @foreach([5,4,3,2,1] as $s)
          @php $count = $distribution[$s] ?? 0; $pct = $totalRatings > 0 ? ($count / $totalRatings * 100) : 0; @endphp
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-bold text-amber-500 w-3">{{ $s }}</span>
            <svg class="w-3 h-3 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
              <div class="h-full rounded-full bg-amber-400 transition-all" style="width:{{ $pct }}%"></div>
            </div>
            <span class="text-[11px] text-slate-400 w-5 text-right">{{ $count }}</span>
          </div>
        @endforeach
      </div>
    </div>

  </div>

  {{-- ── Filter tabs ── --}}
  <div class="flex items-center gap-2 mb-5 flex-wrap">
    <a href="{{ route('admin.ratings') }}"
       class="px-3.5 py-2 rounded-xl text-[12px] font-semibold border transition-all {{ !request('score') ? 'text-white border-transparent' : 'bg-white border-slate-200 text-slate-600 hover:border-orange-300' }}"
       style="{{ !request('score') ? 'background:#FF6B35;' : '' }}">Todas</a>
    @foreach([5,4,3,2,1] as $s)
      <a href="{{ route('admin.ratings', ['score' => $s]) }}"
         class="flex items-center gap-1 px-3.5 py-2 rounded-xl text-[12px] font-semibold border transition-all {{ request('score') == $s ? 'text-white border-transparent' : 'bg-white border-slate-200 text-slate-600 hover:border-orange-300' }}"
         style="{{ request('score') == $s ? 'background:#FF6B35;' : '' }}">
        {{ $s }} ★
      </a>
    @endforeach
  </div>

  {{-- ── Ratings list ── --}}
  @if($ratings->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
      <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-4">⭐</div>
      <p class="text-[15px] font-bold text-slate-700 mb-1">Sin reseñas todavía</p>
      <p class="text-[13px] text-slate-400">Las calificaciones de los clientes aparecerán aquí.</p>
    </div>
  @else
    <div class="flex flex-col gap-3">
      @foreach($ratings as $rating)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4 flex items-start gap-4">

          {{-- Avatar --}}
          <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
               style="background:linear-gradient(135deg,#FF6B35,#E8521A);">
            {{ strtoupper(substr($rating->user?->name ?? '?', 0, 1)) }}
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-[13px] font-bold text-slate-800">{{ $rating->user?->name ?? 'Cliente eliminado' }}</p>
                @if($rating->order)
                  <p class="text-[11px] text-slate-400 mt-0.5">Pedido #{{ $rating->order->order_number }}</p>
                @endif
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-[13px] font-extrabold px-2.5 py-1 rounded-lg {{ $scoreColor($rating->score) }}">
                  {{ $rating->score }}.0
                </span>
                <span class="text-amber-400 text-[14px] tracking-tight">
                  @for($i = 1; $i <= 5; $i++)
                    @if($i <= $rating->score)★@else☆@endif
                  @endfor
                </span>
              </div>
            </div>

            @if($rating->comment)
              <p class="text-[13px] text-slate-600 mt-2 leading-relaxed">{{ $rating->comment }}</p>
            @else
              <p class="text-[12px] text-slate-300 mt-2 italic">Sin comentario</p>
            @endif

            <p class="text-[11px] text-slate-400 mt-2">
              {{ \Carbon\Carbon::parse($rating->created_at)->diffForHumans() }}
            </p>
          </div>

        </div>
      @endforeach
    </div>

    {{-- Pagination --}}
    @if($ratings->hasPages())
      <div class="mt-5 flex items-center justify-between">
        <p class="text-[12px] text-slate-400">
          Mostrando {{ $ratings->firstItem() }}–{{ $ratings->lastItem() }} de {{ $ratings->total() }}
        </p>
        <div class="flex gap-1">
          @if($ratings->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-[12px] text-slate-300 border border-slate-100">← Anterior</span>
          @else
            <a href="{{ $ratings->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-[12px] text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">← Anterior</a>
          @endif
          @if($ratings->hasMorePages())
            <a href="{{ $ratings->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-[12px] text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">Siguiente →</a>
          @else
            <span class="px-3 py-1.5 rounded-lg text-[12px] text-slate-300 border border-slate-100">Siguiente →</span>
          @endif
        </div>
      </div>
    @endif
  @endif

@endif

@endsection
