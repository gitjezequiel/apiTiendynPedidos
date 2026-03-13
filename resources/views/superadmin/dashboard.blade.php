@extends('superadmin.layouts.app')
@section('title', 'Dashboard — FoodTiendyn Sistema')
@section('topbar-title', 'Dashboard del Sistema')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  @php
  $stats = [
    ['label'=>'Restaurantes',  'value'=> $totalRestaurants, 'icon'=>'🏪', 'color'=>'#6366f1'],
    ['label'=>'Propietarios',  'value'=> $totalOwners,      'icon'=>'👨‍🍳','color'=>'#8b5cf6'],
    ['label'=>'Clientes',      'value'=> $totalCustomers,   'icon'=>'👥', 'color'=>'#06b6d4'],
    ['label'=>'Pedidos total', 'value'=> $totalOrders,      'icon'=>'📦', 'color'=>'#10b981'],
  ];
  @endphp

  @foreach($stats as $s)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
           style="background:{{ $s['color'] }}18;">{{ $s['icon'] }}</div>
      <div>
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">{{ $s['label'] }}</p>
        <p class="text-[26px] font-extrabold text-slate-800 leading-tight">{{ number_format($s['value']) }}</p>
      </div>
    </div>
  @endforeach

</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

  {{-- Recent restaurants --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
      <p class="text-[14px] font-bold text-slate-800">Restaurantes recientes</p>
      <a href="{{ route('superadmin.restaurants') }}" class="text-[12px] font-semibold text-indigo-500 hover:underline">Ver todos</a>
    </div>
    <div class="divide-y divide-slate-50">
      @forelse($recentRestaurants as $rest)
        <div class="flex items-center gap-3 px-5 py-3.5">
          <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center text-base overflow-hidden"
               style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
            @if($rest->logo_url)
              <img src="{{ $rest->logo_url }}" class="w-full h-full object-cover">
            @else
              🏪
            @endif
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[13px] font-semibold text-slate-800 truncate">{{ $rest->name }}</p>
            <p class="text-[11px] text-slate-400 truncate">{{ $rest->owner?->email }}</p>
          </div>
          <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $rest->is_open ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
            {{ $rest->is_open ? 'Abierto' : 'Cerrado' }}
          </span>
        </div>
      @empty
        <p class="px-5 py-8 text-center text-[13px] text-slate-400">Sin restaurantes registrados.</p>
      @endforelse
    </div>
  </div>

  {{-- Recent orders --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <p class="text-[14px] font-bold text-slate-800">Pedidos recientes</p>
    </div>
    <div class="divide-y divide-slate-50">
      @forelse($recentOrders as $order)
        @php
          $statusColors = [
            'pendiente'  => 'bg-amber-50 text-amber-700',
            'preparando' => 'bg-blue-50 text-blue-700',
            'listo'      => 'bg-indigo-50 text-indigo-700',
            'entregado'  => 'bg-emerald-50 text-emerald-700',
            'cancelado'  => 'bg-red-50 text-red-600',
            'rechazado'  => 'bg-red-50 text-red-600',
          ];
        @endphp
        <div class="flex items-center gap-3 px-5 py-3">
          <div class="flex-1 min-w-0">
            <p class="text-[12px] font-bold text-slate-800 truncate">{{ $order->order_number }}</p>
            <p class="text-[11px] text-slate-400 truncate">{{ $order->user?->name }} · {{ $order->restaurant?->name }}</p>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-[12px] font-bold text-slate-700">L. {{ number_format($order->total, 2) }}</span>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusColors[$order->status] ?? 'bg-slate-100 text-slate-600' }}">
              {{ ucfirst($order->status) }}
            </span>
          </div>
        </div>
      @empty
        <p class="px-5 py-8 text-center text-[13px] text-slate-400">Sin pedidos todavía.</p>
      @endforelse
    </div>
  </div>

</div>

@endsection
