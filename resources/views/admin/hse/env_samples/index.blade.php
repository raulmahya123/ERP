{{-- resources/views/admin/hse/environmental_samples/index.blade.php --}}
@extends('layouts.app')

@section('title','HSE — Environmental Samples')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">HSE — Environmental Samples</h1>
            <p class="text-white/90 text-sm mt-1">Daftar sampel lingkungan (air, udara, tanah, dll).</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total:
              {{ method_exists($items,'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset
          <a href="{{ route('admin.hse.environmental-samples.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" class="grid gap-3 sm:grid-cols-[1fr_auto]">
      <div class="relative">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode / lokasi / jenis…"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-24 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
        </svg>
        <button class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-sky-700 text-white ring-1 ring-white/30 hover:bg-sky-600 transition">
          Cari
        </button>
      </div>
      @if(request()->filled('q'))
        <a href="{{ route('admin.hse.environmental-samples.index') }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">
          Reset
        </a>
      @endif
    </form>
  </div>

  {{-- FLASH --}}
  @if (session('success'))
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- TABLE --}}
  <div class="p-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Code</th>
              <th class="px-4 py-3 text-left font-semibold">Type</th>
              <th class="px-4 py-3 text-left font-semibold">Location</th>
              <th class="px-4 py-3 text-left font-semibold">Collected At</th>
              <th class="px-4 py-3 text-left font-semibold">Status</th>
              <th class="px-4 py-3 text-center font-semibold w-40">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($items ?? [] as $s)
              <tr class="hover:bg-emerald-50/40">
                <td class="px-4 py-3 font-mono text-emerald-700">{{ $s->code ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-900">{{ strtoupper($s->type ?? '—') }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $s->location ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-700">
                  {{ optional($s->collected_at)->format('Y-m-d H:i') ?? '—' }}
                </td>
                <td class="px-4 py-3">
                  @php $st = $s->status ?? 'draft'; @endphp
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                    @class([
                      'bg-slate-100 text-slate-700 ring-1 ring-slate-200' => $st==='draft',
                      'bg-amber-50 text-amber-800 ring-1 ring-amber-200' => $st==='pending',
                      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' => $st==='approved',
                      'bg-rose-50 text-rose-700 ring-1 ring-rose-200' => $st==='rejected',
                    ])">
                    {{ ucfirst($st) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    <a href="{{ route('admin.hse.environmental-samples.show', $s) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
                      Detail
                    </a>
                    <a href="{{ route('admin.hse.environmental-samples.edit', $s) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">
                      Edit
                    </a>
                    <button type="button"
                            onclick="confirmDeleteSample(this)"
                            data-id="{{ $s->id }}"
                            data-code="{{ e($s->code ?? $s->id) }}"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                      Hapus
                    </button>
                    <form id="del-sample-{{ $s->id }}" action="{{ route('admin.hse.environmental-samples.destroy', $s) }}" method="POST" class="hidden">
                      @csrf @method('DELETE')
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center gap-2 text-slate-600">
                    <svg class="h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <circle cx="11" cy="11" r="7" stroke-width="1.7"></circle>
                      <path d="m20 20-3.5-3.5" stroke-width="1.7" stroke-linecap="round"></path>
                    </svg>
                    <div class="text-sm">Belum ada data sampel.</div>
                    <a href="{{ route('admin.hse.environmental-samples.create') }}"
                       class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 underline">
                      Tambah sekarang
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @isset($items)
      <div class="px-4 py-4 border-t bg-slate-50">
        {{ $items->withQueryString()->onEachSide(1)->links() }}
      </div>
      @endisset
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteSample(el){
  const id   = el.dataset.id;
  const code = el.dataset.code || '';
  if (typeof Swal === 'undefined') {
    if (confirm('Hapus sample: ' + code + ' ?')) {
      document.getElementById('del-sample-' + id).submit();
    }
    return;
  }
  Swal.fire({
    title: 'Hapus Sample?',
    text: 'Apakah kamu yakin ingin menghapus sample: ' + code + ' ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626', // red-600
    cancelButtonColor: '#0284c7',  // sky-600
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){ document.getElementById('del-sample-'+id).submit(); }});
}
</script>
@endpush
