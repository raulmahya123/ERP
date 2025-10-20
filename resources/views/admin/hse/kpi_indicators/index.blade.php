{{-- resources/views/admin/hse/kpi_indicators/index.blade.php --}}
@extends('layouts.app')

@section('title','HSE — KPI Indicators')

@section('content')
@php
  use Illuminate\Support\Str;
  $type     = $type     ?? (string) request('type', '');
  $from     = $from     ?? (string) request('from', '');
  $to       = $to       ?? (string) request('to', '');
  $q        = $q        ?? (string) request('q', '');
  $defCode  = $defCode  ?? (string) request('def', '');
  $defGroup = $defGroup ?? (string) request('group', '');
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white flex items-center justify-between">
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
          <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M3 6h18M3 18h18"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">HSE — KPI Indicators</h1>
          <p class="text-white/90 text-sm mt-1">Leading, Lagging, dan Operational indicators.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        @isset($items)
          <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
            <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
            Total: {{ method_exists($items,'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
          </span>
        @endisset
        <a href="{{ route('admin.hse.kpi-indicators.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          New
        </a>
      </div>
    </div>
  </div>

  {{-- FILTER BAR (ringkas & cepat) --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" class="grid gap-3 md:grid-cols-6">
      <div>
        <select name="type" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— All Types —</option>
          @foreach(['leading','lagging','operational'] as $t)
            <option value="{{ $t }}" @selected($type===$t)>{{ Str::upper($t) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <input type="text" name="def" value="{{ $defCode }}" placeholder="Def. code (ex: LTI)"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>
      <div>
        <select name="group" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— All Groups —</option>
          @foreach(['leading','lagging','base','operational','environment','health','custom'] as $g)
            <option value="{{ $g }}" @selected($defGroup===$g)>{{ Str::upper($g) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <input type="date" name="from" value="{{ $from }}"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>
      <div>
        <input type="date" name="to" value="{{ $to }}"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>
      <div class="flex gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari name/unit/notes/def"
               class="flex-1 rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Filter
        </button>
        @if(request()->hasAny(['type','from','to','q','def','group']))
          <a href="{{ route('admin.hse.kpi-indicators.index') }}" class="text-sm text-slate-500 hover:text-slate-700 self-center">Reset</a>
        @endif
      </div>
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
              <th class="text-left px-4 py-3 font-semibold">Date</th>
              <th class="text-left px-4 py-3 font-semibold">Type</th>
              <th class="text-left px-4 py-3 font-semibold">Definition / Name</th>
              <th class="text-right px-4 py-3 font-semibold">Value</th>
              <th class="text-left px-4 py-3 font-semibold">Unit</th>
              <th class="text-left px-4 py-3 font-semibold">Notes</th>
              <th class="text-center px-4 py-3 font-semibold w-44">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse(($items ?? []) as $row)
              @php
                $typeLower = strtolower($row->type ?? '');
                $val = is_null($row->value) ? '—' : rtrim(rtrim(number_format((float)$row->value, 4, '.', ''), '0'), '.');
                $def  = $row->definition ?? null;
                $defCodeTxt = $def?->code ? '['.$def->code.'] ' : '';
                $nameTxt = $def?->name ?: ($row->name ?? '—');
                $unitTxt = $row->unit ?: ($def->unit ?? '—');
              @endphp
              <tr class="hover:bg-emerald-50/40">
                <td class="px-4 py-3 text-slate-700">{{ optional($row->date)->format('Y-m-d') ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                    @class([
                      'bg-amber-50 text-amber-800 ring-1 ring-amber-200'    => $typeLower==='leading',
                      'bg-sky-50 text-sky-700 ring-1 ring-sky-200'          => $typeLower==='lagging',
                      'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200' => $typeLower==='operational',
                      'bg-slate-100 text-slate-700 ring-1 ring-slate-200'   => !in_array($typeLower,['leading','lagging','operational']),
                    ])">
                    {{ Str::upper($row->type ?? '—') }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">{{ $defCodeTxt }}{{ $nameTxt }}</div>
                  @if(!$def && $row->definition_id)
                    <div class="text-xs text-rose-600 mt-0.5">Definition hilang / tidak valid</div>
                  @endif
                </td>
                <td class="px-4 py-3 text-right text-slate-900 tabular-nums">{{ $val }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $unitTxt }}</td>
                <td class="px-4 py-3 text-slate-600">{{ Str::limit($row->notes ?? '—', 90) }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    @if (Route::has('admin.hse.kpi-indicators.show'))
                      <a href="{{ route('admin.hse.kpi-indicators.show',$row) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50 transition">
                        Detail
                      </a>
                    @endif
                    <a href="{{ route('admin.hse.kpi-indicators.edit',$row) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
                      Edit
                    </a>
                    <button type="button"
                            onclick="confirmDeleteKpi(this)"
                            data-id="{{ $row->id }}"
                            data-name="{{ e($nameTxt) }}"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 transition">
                      Hapus
                    </button>
                    <form id="del-kpi-{{ $row->id }}" action="{{ route('admin.hse.kpi-indicators.destroy',$row) }}" method="POST" class="hidden">
                      @csrf @method('DELETE')
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-600">
                  No data. <a href="{{ route('admin.hse.kpi-indicators.create') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 underline">Create one</a>.
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
function confirmDeleteKpi(el){
  const id   = el.dataset.id;
  const name = el.dataset.name || '';
  if (typeof Swal === 'undefined') {
    if (confirm('Delete KPI: ' + name + ' ?')) {
      document.getElementById('del-kpi-' + id).submit();
    }
    return;
  }
  Swal.fire({
    title: 'Hapus KPI?',
    text: 'Apakah kamu yakin ingin menghapus: ' + name + ' ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#0ea5e9',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){ document.getElementById('del-kpi-'+id).submit(); }});
}
</script>
@endpush
