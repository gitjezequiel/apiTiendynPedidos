@extends('admin.layouts.app')

@section('title', 'Menú — TiendynFood Admin')
@section('topbar-title', 'Menú')

@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Menú</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">
      {{ $restaurant ? $restaurant->name . ' — Gestiona tus categorías y platos' : 'Gestiona el menú de tu restaurante' }}
    </p>
  </div>
  @if($restaurant)
    @php $allCategories = $restaurant->categories()->with('items')->get(); @endphp
    <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-white border border-slate-200 shadow-sm">
      <span class="text-[12px] text-slate-500 font-medium">Platos:</span>
      <span class="text-[15px] font-bold text-slate-800">{{ $allCategories->sum(fn($c) => $c->items->count()) }}</span>
      <span class="text-[11px] text-slate-400">en {{ $allCategories->count() }} categoría{{ $allCategories->count() !== 1 ? 's' : '' }}</span>
    </div>
  @endif
</div>

@if (!$restaurant)
  <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-base mt-0.5">⚠️</div>
    <div>
      <p class="text-sm font-semibold text-amber-800">Sin restaurante registrado</p>
      <p class="text-[13px] text-amber-700 mt-0.5">No tienes un restaurante en el sistema.</p>
    </div>
  </div>
@else

@php $categories = $restaurant->categories()->with('items')->get(); @endphp

@if($categories->isEmpty())
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-4">🍽️</div>
    <p class="text-[15px] font-bold text-slate-700 mb-1">Sin categorías en el menú</p>
    <p class="text-[13px] text-slate-400">Agrega categorías y platillos desde la app móvil o la API.</p>
  </div>
@else

  <div class="flex flex-col gap-5">
    @foreach($categories as $category)

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" id="cat-wrap-{{ $category->id }}">

        {{-- ── Category header ── --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 cursor-pointer hover:bg-slate-50 transition-colors"
             onclick="toggleCat({{ $category->id }})">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0"
                 style="background:linear-gradient(135deg,#fff3ee,#ffe4d6);">
              🍴
            </div>
            <div>
              <p class="text-[15px] font-bold text-slate-800">{{ $category->name }}</p>
              <p class="text-[12px] text-slate-400 mt-0.5">
                {{ $category->items->count() }} {{ $category->items->count() === 1 ? 'producto' : 'productos' }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
              {{ $category->items->count() }} ítems
            </span>
            <span id="chevron-{{ $category->id }}"
                  class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 transition-transform duration-200 flex-shrink-0">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </span>
          </div>
        </div>

        {{-- ── Items grid ── --}}
        <div id="cat-items-{{ $category->id }}">
          @if($category->items->isEmpty())
            <div class="px-5 py-8 text-center">
              <p class="text-[13px] text-slate-400">Esta categoría no tiene productos todavía.</p>
            </div>
          @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
              @foreach($category->items as $item)

                <div class="group rounded-xl border border-slate-100 overflow-hidden bg-white transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-orange-200">

                  {{-- Item image --}}
                  <div class="h-32 bg-slate-50 flex items-center justify-center text-4xl overflow-hidden relative">
                    @if($item->image_url)
                      <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                    @else
                      <span class="text-4xl select-none">🍽️</span>
                    @endif
                    {{-- Availability overlay badge --}}
                    <div class="absolute top-2 right-2">
                      <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $item->is_available ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }}">
                        {{ $item->is_available ? 'Disponible' : 'No disp.' }}
                      </span>
                    </div>
                  </div>

                  {{-- Item body --}}
                  <div class="p-3">
                    <p class="text-[13px] font-bold text-slate-800 leading-tight truncate">{{ $item->name }}</p>

                    @if($item->description)
                      <p class="text-[11px] text-slate-400 mt-1 leading-relaxed line-clamp-2">{{ $item->description }}</p>
                    @endif

                    <div class="flex items-center justify-between mt-2.5">
                      <span class="text-[14px] font-extrabold" style="color:#FF6B35;">L.&nbsp;{{ number_format($item->price, 2) }}</span>
                      @if(isset($item->stock) && $item->stock !== null)
                        <span class="text-[10px] text-slate-400 font-medium bg-slate-100 px-2 py-0.5 rounded-full">
                          Stock: {{ $item->stock }}
                        </span>
                      @endif
                    </div>
                  </div>

                </div>

              @endforeach
            </div>
          @endif
        </div>

      </div>

    @endforeach
  </div>

@endif

@endif

@endsection

@push('scripts')
<script>
  function toggleCat(id) {
    const panel   = document.getElementById('cat-items-' + id);
    const chevron = document.getElementById('chevron-' + id);
    if (panel.style.display === 'none') {
      panel.style.display = '';
      chevron.style.transform = 'rotate(0deg)';
    } else {
      panel.style.display = 'none';
      chevron.style.transform = 'rotate(-90deg)';
    }
  }
</script>
@endpush
