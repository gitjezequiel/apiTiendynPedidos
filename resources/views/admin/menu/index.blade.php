@extends('admin.layouts.app')

@section('title', 'Menú — TiendynFood Admin')

@section('content')

@if (!$restaurant)
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-5 text-sm">
        No tienes un restaurante registrado.
    </div>
@else

{{-- Header --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Menú</h1>
    <p class="text-gray-500 text-sm mt-1">{{ $restaurant->name }}</p>
</div>

@php $categories = $restaurant->categories; @endphp

@if($categories->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-16 text-center text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <p class="text-sm font-medium">Sin categorías en el menú</p>
        <p class="text-xs mt-1">Agrega categorías y platillos desde la app móvil o la API.</p>
    </div>
@else
    <div class="space-y-4" x-data="{ openCats: {} }">
        @foreach($categories as $category)
        @php $catId = 'cat-' . $category->id; @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Category header (accordion toggle) --}}
            <button
                type="button"
                onclick="toggleCategory('{{ $catId }}')"
                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: rgba(255,107,53,0.12);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color: #FF6B35;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-800">{{ $category->name }}</span>
                        <span class="ml-2 text-xs text-gray-400 font-normal">
                            {{ $category->items->count() }} {{ $category->items->count() === 1 ? 'producto' : 'productos' }}
                        </span>
                    </div>
                </div>
                <svg id="chevron-{{ $catId }}" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {{-- Category items --}}
            <div id="{{ $catId }}" class="border-t border-gray-100">
                @if($category->items->isEmpty())
                    <div class="px-6 py-5 text-sm text-gray-400 text-center">
                        Esta categoría no tiene productos todavía.
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-4">
                        @foreach($category->items as $item)
                        <div class="border border-gray-100 rounded-xl overflow-hidden hover:shadow-md transition-shadow bg-gray-50">

                            {{-- Item image --}}
                            @if($item->image_url)
                                <div class="w-full h-36 overflow-hidden bg-gray-200">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-full h-36 flex items-center justify-center bg-gray-100 text-4xl">
                                    {{ $item->emoji ?? '🍽️' }}
                                </div>
                            @endif

                            {{-- Item info --}}
                            <div class="p-3">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <h3 class="font-semibold text-gray-800 text-sm leading-snug">{{ $item->name }}</h3>
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0
                                        {{ $item->is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                        {{ $item->is_available ? 'Disponible' : 'No disponible' }}
                                    </span>
                                </div>

                                @if($item->description)
                                    <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ $item->description }}</p>
                                @endif

                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-sm font-bold" style="color: #FF6B35;">
                                        L. {{ number_format($item->price, 2) }}
                                    </span>
                                    @if(isset($item->stock) && $item->stock !== null)
                                    <span class="text-xs text-gray-400">Stock: {{ $item->stock }}</span>
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

<script>
    // Start all categories open
    document.addEventListener('DOMContentLoaded', () => {
        // They are open by default (no hidden class)
    });

    function toggleCategory(id) {
        const panel = document.getElementById(id);
        const chevron = document.getElementById('chevron-' + id);
        const isHidden = panel.classList.contains('hidden');

        if (isHidden) {
            panel.classList.remove('hidden');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            panel.classList.add('hidden');
            chevron.style.transform = 'rotate(-90deg)';
        }
    }
</script>

@endsection
