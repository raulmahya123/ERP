{{-- resources/views/admin/hse/hse-inspection-reports/index.blade.php --}}
@extends('layouts.app')

@section('title','HSE — Inspection Reports')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">HSE — Inspection Reports</h1>
            <p class="text-white/90 text-sm mt-1">Daftar laporan inspeksi, temuan, dan rekomendasi.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total: {{ method_exists($items,'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset

          @can('create', \App\Models\Hse\HseInspectionReport::class)
            <a href="{{ route('admin.hse.inspection-reports.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              New
            </a>
          @endcan
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  @php
    $q      = request('q');
    $filterStatus = request('status');
  @endphp
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" class="grid gap-3 lg:grid-cols-[1fr_220px_auto]">
      <div class="relative">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari report number / tipe / lokasi…"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-20 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
        </svg>
        @if(request()->filled('q'))
          <a href="{{ route('admin.hse.inspection-reports.index') }}"
             class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-white bg-sky-700 px-2 py-0.5 rounded-lg ring-1 ring-white/30 hover:bg-sky-600">
            Reset
          </a>
        @endif
      </div>

      <select name="status"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Semua Status —</option>
        @foreach (['draft','submitted','verified','closed'] as $st)
          <option value="{{ $st }}" @selected($filterStatus===$st)>{{ \Illuminate\Support\Str::headline($st) }}</option>
        @endforeach
      </select>

      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['q','status']))
          <a href="{{ route('admin.hse.inspection-reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  {{-- FLASH --}}  @if ($errors->any())
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
              <th class="text-left px-4 py-3 font-semibold">Report Number</th>
              <th class="text-left px-4 py-3 font-semibold">Tipe Inspeksi</th>
              <th class="text-left px-4 py-3 font-semibold">Lokasi</th>
              <th class="text-left px-4 py-3 font-semibold">Tanggal Inspeksi</th>
              <th class="text-left px-4 py-3 font-semibold">Status</th>
              <th class="text-center px-4 py-3 font-semibold w-44">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($items as $row)
              @php
                $st = strtolower($row->status ?? 'draft');
                $tz = config('app.timezone','Asia/Jakarta');
              @endphp
              <tr class="hover:bg-emerald-50/40">
                <td class="px-4 py-3 font-mono text-emerald-700">{{ $row->report_number ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-900">{{ $row->inspection_type ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $row->location ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $row->inspection_date ? \Illuminate\Support\Carbon::parse($row->inspection_date)->format('Y-m-d') : '—' }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                    @class([
                      'bg-slate-100 text-slate-700 ring-1 ring-slate-200'  => $st==='draft',
                      'bg-sky-50 text-sky-700 ring-1 ring-sky-200'         => $st==='submitted',
                      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' => $st==='verified',
                      'bg-amber-50 text-amber-800 ring-1 ring-amber-200'   => $st==='closed',
                      'bg-slate-100 text-slate-700 ring-1 ring-slate-200'  => !in_array($st,['draft','submitted','verified','closed']),
                    ])">
                    {{ \Illuminate\Support\Str::headline($st) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    @can('update', $row)
                      <a href="{{ route('admin.hse.inspection-reports.edit', $row) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">
                        Edit
                      </a>
                    @endcan
                    @can('delete', $row)
                      <button type="button"
                              onclick="confirmDeleteInspection(this)"
                              data-id="{{ $row->id }}"
                              data-code="{{ e($row->report_number ?? $row->id) }}"
                              class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                        Hapus
                      </button>
                      <form id="del-inspection-{{ $row->id }}" action="{{ route('admin.hse.inspection-reports.destroy', $row) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                      </form>
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-600">
                  Belum ada inspection report.
                  @can('create', \App\Models\Hse\HseInspectionReport::class)
                    <a href="{{ route('admin.hse.inspection-reports.create') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 underline">Tambah sekarang</a>.
                  @endcan
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="px-4 py-4 border-t bg-slate-50">
        @if (method_exists($items,'withQueryString'))
          {{ $items->withQueryString()->onEachSide(1)->links() }}
        @elseif (method_exists($items,'links'))
          {{ $items->links() }}
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')

<script>
function confirmDeleteInspection(el){
  const id   = el?.dataset?.id;
  const code = el?.dataset?.code || '';
  if (!id) return;

  if (typeof Swal === 'undefined' || !Swal?.fire) {
    if (confirm('Hapus inspection report: ' + code + ' ?')) {
      const f = document.getElementById('del-inspection-' + id);
      if (f) f.submit();
    }
    return;
  }

  Swal.fire({
    title: 'Hapus Inspection Report?',
    text: 'Apakah kamu yakin ingin menghapus: ' + code + ' ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#0284c7',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){
      const f = document.getElementById('del-inspection-'+id);
      if (f) f.submit();
    }});
}
</script>
@endpush
