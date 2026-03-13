@extends('superadmin.layouts.app')
@section('title', 'Medios de Pago — FoodTiendyn Sistema')
@section('topbar-title', 'Medios de Pago')

@section('content')

<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Medios de pago</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">Métodos de pago disponibles para los restaurantes</p>
  </div>
  <button onclick="openModal()"
          class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-[13px] font-semibold text-white hover:opacity-90 transition-opacity"
          style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Nuevo medio
  </button>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
  @forelse($methods as $method)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0 bg-indigo-50">
        {{ $method->icon ?: '💳' }}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-[14px] font-bold text-slate-800">{{ $method->name }}</p>
        <p class="text-[11px] text-slate-400">{{ $method->restaurants_count }} restaurante{{ $method->restaurants_count !== 1 ? 's' : '' }}</p>
      </div>
      <div class="flex gap-1.5 flex-shrink-0">
        <button onclick='openModal({{ $method->id }}, {{ json_encode($method->name) }}, {{ json_encode($method->icon) }})'
                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-indigo-50 flex items-center justify-center transition-colors">
          <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </button>
        <form method="POST" action="{{ route('superadmin.payment-methods.destroy', $method->id) }}" onsubmit="return confirm('¿Eliminar este medio de pago?')">
          @csrf @method('DELETE')
          <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center transition-colors">
            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </form>
      </div>
    </div>
  @empty
    <div class="col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
      <p class="text-[15px] font-bold text-slate-700 mb-1">Sin medios de pago</p>
      <p class="text-[13px] text-slate-400">Crea el primer medio de pago.</p>
    </div>
  @endforelse
</div>

{{-- Modal --}}
<div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h2 id="modalTitle" class="text-[16px] font-bold text-slate-800">Nuevo medio de pago</h2>
      <button onclick="closeModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="modalForm" method="POST" class="px-6 py-5 flex flex-col gap-4">
      @csrf
      <span id="methodField"></span>
      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Nombre</label>
        <input id="fName" type="text" name="name" placeholder="Ej. Efectivo, Transferencia…"
               class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
      </div>
      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Emoji / Ícono <span class="text-slate-400 font-normal">(opcional)</span></label>
        <input id="fIcon" type="text" name="icon" placeholder="💳"
               class="w-full px-3.5 py-2.5 text-[20px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
      </div>
      <div class="flex gap-3 pt-1">
        <button type="button" onclick="closeModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-[13px] font-semibold text-slate-600 hover:bg-slate-50">Cancelar</button>
        <button type="submit" class="flex-1 py-2.5 rounded-xl text-[13px] font-semibold text-white hover:opacity-90" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">Guardar</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
const storeUrl = '{{ route("superadmin.payment-methods.store") }}';
function openModal(id = null, name = '', icon = '') {
  document.getElementById('modalTitle').textContent = id ? 'Editar medio de pago' : 'Nuevo medio de pago';
  document.getElementById('fName').value = name;
  document.getElementById('fIcon').value = icon || '';
  const form = document.getElementById('modalForm');
  form.action = id ? `/superadmin/payment-methods/${id}` : storeUrl;
  document.getElementById('methodField').innerHTML = id ? '<input type="hidden" name="_method" value="PATCH">' : '';
  const m = document.getElementById('modal');
  m.style.display = 'flex'; m.classList.remove('hidden');
}
function closeModal() {
  const m = document.getElementById('modal');
  m.style.display = 'none'; m.classList.add('hidden');
}
document.getElementById('modal').addEventListener('click', function(e) { if(e.target===this) closeModal(); });
</script>
@endpush
