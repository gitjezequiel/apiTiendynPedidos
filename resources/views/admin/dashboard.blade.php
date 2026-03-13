@extends('admin.layouts.app')

@section('title', 'Dashboard — TiendynFood Admin')

@section('content')

{{-- No restaurant alert --}}
@if (!$restaurant)
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-5 flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        <div>
            <p class="font-semibold text-sm">Sin restaurante registrado</p>
            <p class="text-sm mt-0.5">No tienes un restaurante registrado en el sistema. Contacta al administrador para comenzar.</p>
        </div>
    </div>
@else

{{-- Header --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-500 text-sm mt-1">Bienvenido, <span class="font-medium text-gray-700">{{ auth()->user()->name }}</span> — {{ $restaurant->name }}</p>
</div>

{{-- Stats grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">

    {{-- Total pedidos --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: rgba(255,107,53,0.12);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: #FF6B35;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Pedidos</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($totalOrders) }}</p>
        </div>
    </div>

    {{-- Pedidos pendientes --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 bg-yellow-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Pendientes</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($pendingOrders) }}</p>
        </div>
    </div>

    {{-- Pedidos hoy --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 bg-blue-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Pedidos Hoy</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($todayOrders) }}</p>
        </div>
    </div>

    {{-- Ingresos totales --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 bg-green-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Ingresos Totales</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">L. {{ number_format($totalRevenue, 2) }}</p>
        </div>
    </div>

</div>

{{-- Recent orders --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Pedidos Recientes</h2>
        <a href="{{ route('admin.orders') }}" class="text-sm font-medium hover:underline" style="color: #FF6B35;">
            Ver todos
        </a>
    </div>

    @if($recentOrders->isEmpty())
        <div class="py-12 text-center text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-sm">No hay pedidos todavía</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wide bg-gray-50">
                        <th class="px-6 py-3"># Pedido</th>
                        <th class="px-6 py-3">Cliente</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentOrders as $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3.5 font-mono font-medium text-gray-700">
                            #{{ $order->order_number }}
                        </td>
                        <td class="px-6 py-3.5 text-gray-600">
                            {{ $order->user->name ?? 'Cliente eliminado' }}
                        </td>
                        <td class="px-6 py-3.5 font-medium text-gray-800">
                            L. {{ number_format($order->total, 2) }}
                        </td>
                        <td class="px-6 py-3.5">
                            @php
                                $badges = [
                                    'pending'   => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'preparing' => 'bg-orange-100 text-orange-800',
                                    'ready'     => 'bg-green-100 text-green-800',
                                    'delivered' => 'bg-emerald-100 text-emerald-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $labels = [
                                    'pending'   => 'Pendiente',
                                    'confirmed' => 'Confirmado',
                                    'preparing' => 'Preparando',
                                    'ready'     => 'Listo',
                                    'delivered' => 'Entregado',
                                    'cancelled' => 'Cancelado',
                                ];
                                $badgeClass = $badges[$order->status] ?? 'bg-gray-100 text-gray-700';
                                $label = $labels[$order->status] ?? ucfirst($order->status);
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-gray-500 text-xs">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endif

@endsection
