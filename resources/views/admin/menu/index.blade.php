@extends('admin.layouts.app')

@section('title', 'Menú — TiendynFood Admin')
@section('topbar-title', 'Menú')

@section('content')

@php
  $allCategories = $restaurant ? $restaurant->categories()->with('items')->get() : collect();
  $totalItems    = $allCategories->sum(fn($c) => $c->items->count());
@endphp

{{-- ── Flash messages ── --}}
@if(session('success'))
  <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
       class="flex items-center gap-2.5 mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-[13px] font-medium">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div class="flex items-center gap-2.5 mb-5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-[13px] font-medium">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    {{ session('error') }}
  </div>
@endif

{{-- ── Page header ── --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Menú</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">
      {{ $restaurant ? $restaurant->name . ' — ' . $totalItems . ' producto' . ($totalItems !== 1 ? 's' : '') . ' en ' . $allCategories->count() . ' categoría' . ($allCategories->count() !== 1 ? 's' : '') : 'Gestiona el menú de tu restaurante' }}
    </p>
  </div>
  @if($restaurant)
    <button onclick="openCatModal()"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-[13px] font-semibold text-white shadow-sm hover:opacity-90 transition-opacity"
            style="background:#FF6B35;">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Nueva categoría
    </button>
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

  @if($allCategories->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
      <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-4">🍽️</div>
      <p class="text-[15px] font-bold text-slate-700 mb-1">Sin categorías en el menú</p>
      <p class="text-[13px] text-slate-400 mb-5">Empieza creando una categoría para tu menú.</p>
      <button onclick="openCatModal()"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px] font-semibold text-white shadow-sm hover:opacity-90 transition-opacity"
              style="background:#FF6B35;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Crear primera categoría
      </button>
    </div>

  @else

    <div class="flex flex-col gap-4">
      @foreach($allCategories as $cat)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

          {{-- Category header --}}
          <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3 cursor-pointer flex-1 min-w-0" onclick="toggleCat({{ $cat->id }})">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base flex-shrink-0"
                   style="background:linear-gradient(135deg,#fff3ee,#ffe4d6);">🍴</div>
              <div class="min-w-0">
                <p class="text-[15px] font-bold text-slate-800 truncate">{{ $cat->name }}</p>
                <p class="text-[12px] text-slate-400">{{ $cat->items->count() }} {{ $cat->items->count() === 1 ? 'producto' : 'productos' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0 ml-3">
              {{-- Add item button --}}
              <button onclick="openItemModal(null, {{ $cat->id }})"
                      class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-semibold text-white hover:opacity-90 transition-opacity"
                      style="background:#FF6B35;" title="Agregar producto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Producto
              </button>
              {{-- Edit category --}}
              <button onclick="openCatModal({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                      class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors" title="Editar categoría">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              {{-- Delete category --}}
              <button onclick="confirmDeleteCat({{ $cat->id }}, {{ $cat->items->count() }})"
                      class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center transition-colors" title="Eliminar categoría">
                <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
              {{-- Chevron toggle --}}
              <span id="chevron-{{ $cat->id }}" class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 transition-transform duration-200 cursor-pointer"
                    onclick="toggleCat({{ $cat->id }})">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
              </span>
            </div>
          </div>

          {{-- Items grid --}}
          <div id="cat-items-{{ $cat->id }}">
            @if($cat->items->isEmpty())
              <div class="px-5 py-8 text-center">
                <p class="text-[13px] text-slate-400 mb-3">Esta categoría no tiene productos todavía.</p>
                <button onclick="openItemModal(null, {{ $cat->id }})"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-[12px] font-semibold text-white hover:opacity-90 transition-opacity"
                        style="background:#FF6B35;">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                  Agregar primer producto
                </button>
              </div>
            @else
              <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
                @foreach($cat->items as $item)
                  <div class="group rounded-xl border border-slate-100 overflow-hidden bg-white transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-orange-200 flex flex-col">

                    {{-- Image --}}
                    <div class="h-32 bg-slate-50 flex items-center justify-center overflow-hidden relative flex-shrink-0">
                      @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                      @else
                        <span class="text-4xl select-none">🍽️</span>
                      @endif
                      {{-- Availability toggle --}}
                      <div class="absolute top-2 left-2">
                        <button onclick="toggleAvailability({{ $item->id }}, this)"
                                data-available="{{ $item->is_available ? '1' : '0' }}"
                                class="text-[10px] font-bold px-2 py-0.5 rounded-full border transition-all {{ $item->is_available ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200' }}">
                          {{ $item->is_available ? 'Disponible' : 'No disp.' }}
                        </button>
                      </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-3 flex flex-col flex-1">
                      <p class="text-[13px] font-bold text-slate-800 leading-tight truncate">{{ $item->name }}</p>
                      @if($item->description)
                        <p class="text-[11px] text-slate-400 mt-1 leading-relaxed line-clamp-2">{{ $item->description }}</p>
                      @endif
                      <div class="flex items-center justify-between mt-auto pt-2.5">
                        <span class="text-[14px] font-extrabold" style="color:#FF6B35;">L. {{ number_format($item->price, 2) }}</span>
                        @if($item->stock !== null && $item->stock >= 0)
                          <span class="text-[10px] text-slate-400 font-medium bg-slate-100 px-2 py-0.5 rounded-full">Stock: {{ $item->stock }}</span>
                        @endif
                      </div>
                    </div>

                    {{-- Actions footer (visible on hover) --}}
                    <div class="px-3 pb-3 flex gap-2">
                      <button onclick='openItemModal({{ json_encode(["id"=>$item->id,"category_id"=>$item->category_id,"name"=>$item->name,"description"=>$item->description,"price"=>$item->price,"stock"=>$item->stock,"is_available"=>$item->is_available,"image_url"=>$item->image_url]) }}, {{ $item->category_id }})'
                              class="flex-1 py-1.5 rounded-lg bg-slate-100 hover:bg-orange-50 text-[11px] font-semibold text-slate-600 hover:text-orange-600 transition-all flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                      </button>
                      <button onclick="confirmDeleteItem({{ $item->id }}, '{{ addslashes($item->name) }}')"
                              class="flex-1 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-[11px] font-semibold text-red-500 transition-all flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                      </button>
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

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ── MODAL: Category ── --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div id="catModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h2 id="catModalTitle" class="text-[16px] font-bold text-slate-800">Nueva categoría</h2>
      <button onclick="closeCatModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="catForm" method="POST" class="px-6 py-5">
      @csrf
      <span id="catMethodField"></span>
      <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Nombre de la categoría</label>
      <input id="catName" type="text" name="name" placeholder="Ej. Entradas, Bebidas…"
             class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all">
      <div class="flex gap-3 mt-5">
        <button type="button" onclick="closeCatModal()"
                class="flex-1 py-2.5 rounded-xl border border-slate-200 text-[13px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
          Cancelar
        </button>
        <button type="submit"
                class="flex-1 py-2.5 rounded-xl text-[13px] font-semibold text-white hover:opacity-90 transition-opacity"
                style="background:#FF6B35;">
          Guardar
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── Hidden delete forms for categories ── --}}
@foreach($allCategories as $cat)
  <form id="deleteCatForm-{{ $cat->id }}" action="{{ route('admin.menu.categories.destroy', $cat->id) }}" method="POST" class="hidden">
    @csrf @method('DELETE')
  </form>
@endforeach

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ── MODAL: Item ── --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div id="itemModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
      <h2 id="itemModalTitle" class="text-[16px] font-bold text-slate-800">Nuevo producto</h2>
      <button onclick="closeItemModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="itemForm" method="POST" enctype="multipart/form-data" class="px-6 py-5 flex flex-col gap-4">
      @csrf
      <span id="itemMethodField"></span>

      {{-- Category select --}}
      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Categoría</label>
        <select id="itemCategoryId" name="category_id"
                class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all bg-white">
          @foreach($allCategories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Name --}}
      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Nombre del producto</label>
        <input id="itemName" type="text" name="name" placeholder="Ej. Pollo a la plancha"
               class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all">
      </div>

      {{-- Description --}}
      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Descripción <span class="text-slate-400 font-normal">(opcional)</span></label>
        <textarea id="itemDescription" name="description" rows="2" placeholder="Breve descripción del platillo…"
                  class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all resize-none"></textarea>
      </div>

      {{-- Price & Stock --}}
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Precio (L.)</label>
          <input id="itemPrice" type="number" name="price" step="0.01" min="0" placeholder="0.00"
                 class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all">
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Stock <span class="text-slate-400 font-normal">(opcional)</span></label>
          <input id="itemStock" type="number" name="stock" min="0" placeholder="—"
                 class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all">
        </div>
      </div>

      {{-- Image --}}
      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Imagen</label>
        {{-- Preview --}}
        <div id="imgPreviewWrap" class="hidden mb-2">
          <img id="imgPreview" src="" alt="preview" class="w-full h-36 object-cover rounded-xl border border-slate-200">
        </div>
        <div class="flex flex-col gap-2">
          <label class="flex items-center gap-2 px-3.5 py-2.5 border border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-orange-400 hover:bg-orange-50 transition-all text-[13px] text-slate-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Subir imagen desde el dispositivo
            <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImg(this)">
          </label>
          <div class="flex items-center gap-2">
            <div class="flex-1 h-px bg-slate-100"></div>
            <span class="text-[11px] text-slate-400">o pega una URL</span>
            <div class="flex-1 h-px bg-slate-100"></div>
          </div>
          <input id="itemImageUrl" type="text" name="image_url" placeholder="https://…"
                 class="w-full px-3.5 py-2.5 text-[13px] border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all"
                 oninput="previewUrl(this.value)">
        </div>
      </div>

      {{-- Availability toggle --}}
      <div class="flex items-center justify-between py-3 px-4 bg-slate-50 rounded-xl">
        <div>
          <p class="text-[13px] font-semibold text-slate-700">Disponible</p>
          <p class="text-[11px] text-slate-400">Los clientes podrán ver y ordenar este producto</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input id="itemAvailable" type="checkbox" name="is_available" value="1" class="sr-only peer" checked>
          <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-500"></div>
        </label>
      </div>

      <div class="flex gap-3 pt-1">
        <button type="button" onclick="closeItemModal()"
                class="flex-1 py-2.5 rounded-xl border border-slate-200 text-[13px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
          Cancelar
        </button>
        <button type="submit"
                class="flex-1 py-2.5 rounded-xl text-[13px] font-semibold text-white hover:opacity-90 transition-opacity"
                style="background:#FF6B35;">
          Guardar producto
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── Hidden delete form for items (reused) ── --}}
<form id="deleteItemForm" method="POST" class="hidden">
  @csrf @method('DELETE')
</form>

{{-- ── Delete confirm modal ── --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center" onclick="event.stopPropagation()">
    <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
      <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    </div>
    <p id="confirmTitle" class="text-[15px] font-bold text-slate-800 mb-1">¿Eliminar?</p>
    <p id="confirmDesc" class="text-[13px] text-slate-400 mb-5">Esta acción no se puede deshacer.</p>
    <div class="flex gap-3">
      <button onclick="closeConfirm()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-[13px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
        Cancelar
      </button>
      <button id="confirmBtn" onclick="doDelete()"
              class="flex-1 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-[13px] font-semibold text-white transition-colors flex items-center justify-center gap-2">
        <svg id="confirmSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/>
          <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
        <span id="confirmBtnText">Eliminar</span>
      </button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// ── Category URL base
const catStoreUrl  = '{{ route("admin.menu.categories.store") }}';
const itemStoreUrl = '{{ route("admin.menu.items.store") }}';
const csrfToken    = '{{ csrf_token() }}';

// ── Toggle category expand/collapse ──────────────────────────
function toggleCat(id) {
  const panel   = document.getElementById('cat-items-' + id);
  const chevron = document.getElementById('chevron-' + id);
  const hidden  = panel.style.display === 'none';
  panel.style.display  = hidden ? '' : 'none';
  chevron.style.transform = hidden ? 'rotate(0deg)' : 'rotate(-90deg)';
}

// ── Category modal ────────────────────────────────────────────
let editCatId = null;
function openCatModal(id = null, name = '') {
  editCatId = id;
  document.getElementById('catModalTitle').textContent = id ? 'Editar categoría' : 'Nueva categoría';
  document.getElementById('catName').value = name;
  const form = document.getElementById('catForm');
  if (id) {
    form.action = `/admin/menu/categories/${id}`;
    document.getElementById('catMethodField').innerHTML = '<input type="hidden" name="_method" value="PATCH">';
  } else {
    form.action = catStoreUrl;
    document.getElementById('catMethodField').innerHTML = '';
  }
  showModal('catModal');
}
function closeCatModal() { hideModal('catModal'); }

// ── Item modal ────────────────────────────────────────────────
function openItemModal(item, defaultCatId) {
  const isEdit = item && item.id;
  document.getElementById('itemModalTitle').textContent = isEdit ? 'Editar producto' : 'Nuevo producto';

  const form = document.getElementById('itemForm');
  if (isEdit) {
    form.action = `/admin/menu/items/${item.id}`;
    document.getElementById('itemMethodField').innerHTML = '<input type="hidden" name="_method" value="PATCH">';
  } else {
    form.action = itemStoreUrl;
    document.getElementById('itemMethodField').innerHTML = '';
  }

  // Pre-fill fields
  const catId = (isEdit ? item.category_id : defaultCatId) || '';
  document.getElementById('itemCategoryId').value   = catId;
  document.getElementById('itemName').value         = isEdit ? item.name         : '';
  document.getElementById('itemDescription').value  = isEdit ? (item.description || '') : '';
  document.getElementById('itemPrice').value        = isEdit ? item.price        : '';
  document.getElementById('itemStock').value        = isEdit ? (item.stock >= 0 ? item.stock : '') : '';
  document.getElementById('itemAvailable').checked  = isEdit ? !!item.is_available : true;
  document.getElementById('itemImageUrl').value     = isEdit ? (item.image_url || '') : '';

  // Preview
  const imgUrl = isEdit ? (item.image_url || '') : '';
  const wrap   = document.getElementById('imgPreviewWrap');
  const img    = document.getElementById('imgPreview');
  if (imgUrl) {
    img.src = imgUrl; wrap.classList.remove('hidden');
  } else {
    img.src = ''; wrap.classList.add('hidden');
  }

  showModal('itemModal');
}
function closeItemModal() { hideModal('itemModal'); }

// ── Image preview helpers ─────────────────────────────────────
function previewImg(input) {
  if (!input.files || !input.files[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  document.getElementById('imgPreview').src = url;
  document.getElementById('imgPreviewWrap').classList.remove('hidden');
  document.getElementById('itemImageUrl').value = '';
}
function previewUrl(url) {
  const img  = document.getElementById('imgPreview');
  const wrap = document.getElementById('imgPreviewWrap');
  if (url) {
    img.src = url;
    wrap.classList.remove('hidden');
  } else {
    img.src = ''; wrap.classList.add('hidden');
  }
}

// ── Toggle availability (inline AJAX) ────────────────────────
function toggleAvailability(itemId, btn) {
  fetch(`/admin/menu/items/${itemId}/toggle`, {
    method: 'PATCH',
    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(data => {
    const avail = data.is_available;
    btn.dataset.available = avail ? '1' : '0';
    btn.textContent = avail ? 'Disponible' : 'No disp.';
    btn.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full border transition-all ' +
      (avail ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
              : 'bg-red-50 text-red-600 border-red-200');
  });
}

// ── Delete confirm modal ──────────────────────────────────────
let pendingDeleteForm = null;
function confirmDeleteCat(id, itemCount) {
  pendingDeleteForm = document.getElementById('deleteCatForm-' + id);
  document.getElementById('confirmTitle').textContent = 'Eliminar categoría';
  document.getElementById('confirmDesc').textContent  =
    itemCount > 0
      ? `Esta categoría tiene ${itemCount} producto${itemCount !== 1 ? 's' : ''}. Debes eliminarlos primero.`
      : 'Esta acción no se puede deshacer.';
  showModal('confirmModal');
}
function confirmDeleteItem(id, name) {
  const form = document.getElementById('deleteItemForm');
  form.action = `/admin/menu/items/${id}`;
  pendingDeleteForm = form;
  document.getElementById('confirmTitle').textContent = `Eliminar "${name}"`;
  document.getElementById('confirmDesc').textContent  = 'Esta acción no se puede deshacer.';
  showModal('confirmModal');
}
function closeConfirm() { hideModal('confirmModal'); pendingDeleteForm = null; }
function doDelete() {
  if (!pendingDeleteForm) return;
  document.getElementById('confirmSpinner').classList.remove('hidden');
  document.getElementById('confirmBtnText').textContent = 'Eliminando…';
  document.getElementById('confirmBtn').disabled = true;
  document.getElementById('confirmBtn').classList.add('opacity-75', 'cursor-not-allowed');
  pendingDeleteForm.submit();
}

// ── Modal helpers ─────────────────────────────────────────────
function showModal(id) {
  const el = document.getElementById(id);
  el.style.display = 'flex';
  el.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function hideModal(id) {
  const el = document.getElementById(id);
  el.style.display = 'none';
  el.classList.add('hidden');
  document.body.style.overflow = '';
}

// Close modals on backdrop click
['catModal','itemModal','confirmModal'].forEach(id => {
  document.getElementById(id).addEventListener('click', function(e) {
    if (e.target === this) {
      hideModal(id);
      pendingDeleteForm = null;
    }
  });
});
</script>
@endpush
