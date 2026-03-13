@extends('admin.layouts.app')

@section('title', 'Pedidos — TiendynFood Admin')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Pedidos</h1>
    <p class="text-gray-500 text-sm mt-1">Gestiona todos los pedidos de tu restaurante</p>
</div>

@if (!$restaurant)
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-5 text-sm">
        No tienes un restaurante registrado.
    </div>
@else

{{-- Filter tabs --}}
@php
    $statuses = [
        ''          => 'Todos',
        'pending'   => 'Pendientes',
        'confirmed' => 'Confirmados',
        'preparing' => 'Preparando',
        'ready'     => 'Listos',
        'delivered' => 'Entregados',
        'cancelled' => 'Cancelados',
    ];
    $currentStatus = request('status', '');
@endphp

<div class="flex flex-wrap gap-2 mb-5">
    @foreach($statuses as $value => $label)
        <a href="{{ route('admin.orders', $value ? ['status' => $value] : []) }}"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors border
                  {{ $currentStatus === $value
                      ? 'text-white border-transparent'
                      : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300' }}"
           @if($currentStatus === $value) style="background-color: #FF6B35; border-color: #FF6B35;" @endif>
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Orders table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">

    @if($orders->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-sm font-medium">No hay pedidos</p>
            <p class="text-xs mt-1">
                @if($currentStatus) Prueba otro filtro de estado @else Los pedidos aparecerán aquí cuando los clientes ordenen @endif
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wide bg-gray-50">
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">Total</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="orders-tbody">
                    @foreach($orders as $order)
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
                    <tr class="hover:bg-gray-50 transition-colors" id="order-row-{{ $order->id }}">
                        <td class="px-5 py-3.5 font-mono font-medium text-gray-700">
                            #{{ $order->order_number }}
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">
                            {{ $order->user->name ?? 'Cliente eliminado' }}
                        </td>
                        <td class="px-5 py-3.5 font-medium text-gray-800">
                            L. {{ number_format($order->total, 2) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span id="badge-{{ $order->id }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs whitespace-nowrap">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <select
                                    id="select-{{ $order->id }}"
                                    class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-700 focus:outline-none focus:ring-2"
                                    style="min-width:130px;"
                                >
                                    <option value="pending"   {{ $order->status === 'pending'   ? 'selected' : '' }}>Pendiente</option>
                                    <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                    <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>Preparando</option>
                                    <option value="ready"     {{ $order->status === 'ready'     ? 'selected' : '' }}>Listo</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Entregado</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                <button
                                    onclick="updateOrderStatus({{ $order->id }}, '{{ route('admin.orders.status', $order->id) }}')"
                                    class="text-xs text-white px-3 py-1.5 rounded-lg font-medium transition-opacity hover:opacity-80 active:opacity-70"
                                    style="background-color: #FF6B35;"
                                >
                                    Actualizar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
        @endif
    @endif
</div>

@endif

{{-- Toast notification --}}
<div id="toast" class="fixed bottom-5 right-5 z-50 hidden">
    <div id="toast-inner" class="px-4 py-3 rounded-xl text-sm text-white shadow-lg font-medium"></div>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';

    const badgeClasses = {
        pending:   'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        preparing: 'bg-orange-100 text-orange-800',
        ready:     'bg-green-100 text-green-800',
        delivered: 'bg-emerald-100 text-emerald-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    const statusLabels = {
        pending:   'Pendiente',
        confirmed: 'Confirmado',
        preparing: 'Preparando',
        ready:     'Listo',
        delivered: 'Entregado',
        cancelled: 'Cancelado',
    };

    function updateOrderStatus(orderId, url) {
        const select = document.getElementById('select-' + orderId);
        const newStatus = select.value;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: newStatus }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                const badge = document.getElementById('badge-' + orderId);
                badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + (badgeClasses[data.status] || 'bg-gray-100 text-gray-700');
                badge.textContent = statusLabels[data.status] || data.status;
                showToast(data.message || 'Estado actualizado', 'success');
            } else {
                showToast(data.message || 'Error al actualizar', 'error');
            }
        })
        .catch(() => {
            showToast('Error de conexión', 'error');
        });
    }

    function showToast(message, type) {
        const toast = document.getElementById('toast');
        const inner = document.getElementById('toast-inner');
        inner.textContent = message;
        inner.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }
</script>

@endsection
