@extends('superadmin.layouts.app')
@section('title', 'Anuncios — FoodTiendyn Sistema')
@section('topbar-title', 'Anuncios')

@section('content')

<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800">Anuncios</h1>
    <p class="text-[13px] text-slate-400 mt-0.5">Banners y mensajes que aparecen en la app de clientes</p>
  </div>
  <button onclick="openModal()"
          class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-[13px] font-semibold text-white hover:opacity-90 transition-opacity"
          style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Nuevo anuncio
  </button>
</div>

@if($announcements->isEmpty())
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-4">📢</div>
    <p class="text-[15px] font-bold text-slate-700 mb-1">Sin anuncios</p>
    <p class="text-[13px] text-slate-400 mb-5">Crea el primer anuncio para la app.</p>
  </div>
@else
  <div class="flex flex-col gap-4">
    @foreach($announcements as $ann)
      <div class="bg-white rounded-2xl border {{ $ann->is_active ? 'border-slate-100' : 'border-slate-100 opacity-60' }} shadow-sm overflow-hidden flex">

        {{-- Image --}}
        <div class="w-36 flex-shrink-0 bg-slate-100 flex items-center justify-center text-3xl">
          @if($ann->image_url)
            <img src="{{ $ann->image_url }}" class="w-full h-full object-cover">
          @else
            📢
          @endif
        </div>

        {{-- Content --}}
        <div class="flex-1 p-5 flex flex-col justify-between min-w-0">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <p class="text-[14px] font-bold text-slate-800 truncate">{{ $ann->title }}</p>
              <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $ann->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                {{ $ann->is_active ? 'Activo' : 'Inactivo' }}
              </span>
              @if($ann->sort_order > 0)
                <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">Orden: {{ $ann->sort_order }}</span>
              @endif
            </div>
            @if($ann->description)
              <p class="text-[12px] text-slate-500 line-clamp-2">{{ $ann->description }}</p>
            @endif
            @if($ann->link_url)
              <p class="text-[11px] text-indigo-400 mt-1 truncate">🔗 {{ $ann->link_url }}</p>
            @endif
          </div>
          <div class="flex items-center gap-2 mt-3">
            {{-- Toggle --}}
            <form method="POST" action="{{ route('superadmin.announcements.toggle', $ann->id) }}">
              @csrf @method('PATCH')
              <button type="submit" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold border transition-all {{ $ann->is_active ? 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                {{ $ann->is_active ? 'Desactivar' : 'Activar' }}
              </button>
            </form>
            {{-- Edit --}}
            <button onclick='openModal(
                {{ $ann->id }},
                {{ json_encode($ann->title) }},
                {{ json_encode($ann->description) }},
                {{ json_encode($ann->image_url) }},
                {{ json_encode($ann->link_url) }},
                {{ $ann->is_active ? "true" : "false" }},
                {{ $ann->sort_order }})'
                class="px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all">
              Editar
            </button>
            {{-- Delete --}}
            <form method="POST" action="{{ route('superadmin.announcements.destroy', $ann->id) }}" onsubmit="return confirm('¿Eliminar este anuncio?')">
              @csrf @method('DELETE')
              <button type="submit" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-red-50 text-red-500 border border-red-200 hover:bg-red-100 transition-all">Eliminar</button>
            </form>
          </div>
        </div>

      </div>
    @endforeach
  </div>
@endif

{{-- Modal --}}
<div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
      <h2 id="modalTitle" class="text-[16px] font-bold text-slate-800">Nuevo anuncio</h2>
      <button onclick="closeModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="modalForm" method="POST" class="px-6 py-5 flex flex-col gap-4">
      @csrf
      <span id="methodField"></span>

      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Título</label>
        <input id="fTitle" type="text" name="title" placeholder="Ej. ¡Descuento especial este fin de semana!"
               class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
      </div>

      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Descripción <span class="text-slate-400 font-normal">(opcional)</span></label>
        <textarea id="fDesc" name="description" rows="2" placeholder="Texto secundario del anuncio…"
                  class="w-full px-3.5 py-2.5 text-[13px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all resize-none"></textarea>
      </div>

      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">URL de imagen <span class="text-slate-400 font-normal">(opcional)</span></label>
        <input id="fImg" type="text" name="image_url" placeholder="https://…"
               class="w-full px-3.5 py-2.5 text-[13px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
               oninput="updatePreview(this.value)">
        <div id="imgPreviewWrap" class="hidden mt-2">
          <img id="imgPreview" src="" class="w-full h-32 object-cover rounded-xl border border-slate-200">
        </div>
      </div>

      <div>
        <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">URL de enlace <span class="text-slate-400 font-normal">(opcional)</span></label>
        <input id="fLink" type="text" name="link_url" placeholder="https://… (a dónde lleva al hacer clic)"
               class="w-full px-3.5 py-2.5 text-[13px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[12px] font-semibold text-slate-600 mb-1.5">Orden <span class="text-slate-400 font-normal">(0 = primero)</span></label>
          <input id="fOrder" type="number" name="sort_order" min="0" value="0"
                 class="w-full px-3.5 py-2.5 text-[14px] border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
        </div>
        <div class="flex items-end pb-1">
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input id="fActive" type="checkbox" name="is_active" value="1" checked
                   class="w-4 h-4 rounded accent-indigo-600">
            <span class="text-[13px] font-semibold text-slate-700">Activo</span>
          </label>
        </div>
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
const storeUrl = '{{ route("superadmin.announcements.store") }}';

function openModal(id=null, title='', desc='', img='', link='', active=true, order=0) {
  document.getElementById('modalTitle').textContent = id ? 'Editar anuncio' : 'Nuevo anuncio';
  document.getElementById('fTitle').value  = title;
  document.getElementById('fDesc').value   = desc  || '';
  document.getElementById('fImg').value    = img   || '';
  document.getElementById('fLink').value   = link  || '';
  document.getElementById('fOrder').value  = order;
  document.getElementById('fActive').checked = active;
  updatePreview(img || '');
  const form = document.getElementById('modalForm');
  form.action = id ? `/superadmin/announcements/${id}` : storeUrl;
  document.getElementById('methodField').innerHTML = id ? '<input type="hidden" name="_method" value="PATCH">' : '';
  const m = document.getElementById('modal');
  m.style.display = 'flex'; m.classList.remove('hidden');
}
function closeModal() {
  const m = document.getElementById('modal');
  m.style.display = 'none'; m.classList.add('hidden');
}
function updatePreview(url) {
  const wrap = document.getElementById('imgPreviewWrap');
  const img  = document.getElementById('imgPreview');
  if (url) { img.src = url; wrap.classList.remove('hidden'); }
  else      { img.src = ''; wrap.classList.add('hidden'); }
}
document.getElementById('modal').addEventListener('click', function(e) { if(e.target===this) closeModal(); });
</script>
@endpush
