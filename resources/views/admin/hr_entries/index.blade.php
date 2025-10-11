{{-- resources/views/admin/hr_entries/index.blade.php --}}
@extends('layouts.app')
@section('title','HR Daily Entries')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">HR Daily Entries</h1>
      <p class="text-slate-500 text-sm">Izin • Cuti • Sakit • Mutasi Shift</p>
      @isset($activeSiteId)
        <div class="mt-2 inline-flex items-center gap-2 text-xs">
          <span class="px-2 py-0.5 rounded border border-emerald-200 bg-emerald-50 text-emerald-700">
            Site Aktif: <span class="font-medium">{{ $activeSiteId }}</span>
          </span>
          <a href="{{ route('admin.sites.index') ?? '#' }}"
             class="text-slate-500 hover:text-slate-700 underline decoration-dotted">
            ganti site
          </a>
        </div>
      @endisset
    </div>
    <a href="{{ route('admin.hr-entries.create') }}"
       class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
      + Tambah Entry
    </a>
  </div>

  {{-- FLASH --}}
  @if(session('success'))
    <div class="p-3 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="p-3 rounded-md bg-rose-50 text-rose-700 border border-rose-200">{{ session('error') }}</div>
  @endif

  {{-- FILTERS --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
    <form method="get" class="p-3 md:p-4 grid md:grid-cols-6 gap-3">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Type</label>
        <select name="type"
                class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400">
          <option value="">— Semua —</option>
          @foreach($types as $k=>$v)
            <option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Tanggal (exact)</label>
        <input type="date" name="date" value="{{ request('date') }}"
               class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Dari</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Sampai</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Cari (reason/kode)</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="mis. sakit, tugas keluarga…"
               class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400">
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-xs text-slate-500 mb-1">Per halaman</label>
          <select name="per_page"
                  class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400">
            @foreach([10,25,50,100] as $n)
              <option value="{{ $n }}" @selected((int)request('per_page',25)===$n)>{{ $n }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex items-end">
          <button class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-slate-200 bg-white hover:bg-slate-50 w-full">
            Terapkan
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
    <div class="p-3 md:p-4 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr>
            <th class="text-left text-slate-600 font-semibold py-2 border-b border-slate-200">Tanggal</th>
            <th class="text-left text-slate-600 font-semibold py-2 border-b border-slate-200">User</th>
            <th class="text-left text-slate-600 font-semibold py-2 border-b border-slate-200">Type</th>
            <th class="text-left text-slate-600 font-semibold py-2 border-b border-slate-200">Reason</th>
            <th class="text-left text-slate-600 font-semibold py-2 border-b border-slate-200">Shift/Meta</th>
            <th class="text-left text-slate-600 font-semibold py-2 border-b border-slate-200">Kode</th>
            <th class="text-left text-slate-600 font-semibold py-2 border-b border-slate-200">Status</th>
            <th class="text-left text-slate-600 font-semibold py-2 border-b border-slate-200 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($entries as $e)
            @php
              $statusClass = [
                'pending'  => 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800',
                'approved' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800',
                'rejected' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800',
              ][$e->status ?? 'pending'] ?? 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800';

              $tLabel = $types[$e->type] ?? strtoupper($e->type ?? '-');

              // Ringkas meta per type
              $metaChips = [];
              $m = (array) ($e->meta ?? []);
              if ($e->type === 'leave') {
                  if (!empty($m['leave_type'])) $metaChips[] = 'Jenis: '.ucfirst($m['leave_type']);
                  if (!empty($m['duration_days'])) $metaChips[] = 'Durasi: '.$m['duration_days'].' hari';
                  if (!empty($m['half_day'])) $metaChips[] = 'Half-day';
              } elseif ($e->type === 'permit') {
                  if (!empty($m['permit_category'])) $metaChips[] = 'Kategori: '.ucfirst($m['permit_category']);
                  if (!empty($m['hours'])) $metaChips[] = 'Jam: '.$m['hours'];
                  if (!empty($m['start_time']) && !empty($m['end_time'])) $metaChips[] = $m['start_time'].'–'.$m['end_time'];
              } elseif ($e->type === 'sick') {
                  if (!empty($m['diagnosis'])) $metaChips[] = 'Dx: '.$m['diagnosis'];
                  if (!empty($m['doctor_note'])) $metaChips[] = 'Surat Dokter';
                  if (!empty($m['inpatient'])) $metaChips[] = 'Rawat Inap';
              } elseif ($e->type === 'shift_change') {
                  if ($e->fromShift?->name || $e->toShift?->name) {
                      $metaChips[] = ($e->fromShift?->name ?? '-') . ' → ' . ($e->toShift?->name ?? '-');
                  }
                  if (!empty($m['effective_from'])) $metaChips[] = 'Efektif: '.\Illuminate\Support\Carbon::parse($m['effective_from'])->format('Y-m-d');
              }
            @endphp
            <tr>
              <td class="py-2 border-b border-slate-100 whitespace-nowrap">{{ $e->date?->format('Y-m-d') }}</td>
              <td class="py-2 border-b border-slate-100">
                <div class="font-medium">{{ $e->user?->name ?? '-' }}</div>
                <div class="text-xs text-slate-500">{{ $e->site?->name ?? '—' }}</div>
              </td>
              <td class="py-2 border-b border-slate-100">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                  {{ $tLabel }}
                </span>
              </td>
              <td class="py-2 border-b border-slate-100">
                <div class="max-w-xs break-words">{{ $e->reason ?: '—' }}</div>
              </td>
              <td class="py-2 border-b border-slate-100">
                @if($e->type==='shift_change' || !empty($metaChips))
                  <div class="flex flex-wrap gap-1">
                    @foreach($metaChips as $chip)
                      <span class="px-2 py-0.5 rounded text-[11px] bg-slate-50 border border-slate-200 text-slate-600">{{ $chip }}</span>
                    @endforeach
                  </div>
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>
              <td class="py-2 border-b border-slate-100">{{ $e->code ?? '—' }}</td>
              <td class="py-2 border-b border-slate-100">
                <span class="{{ $statusClass }}">{{ ucfirst($e->status ?? 'pending') }}</span>
                @if($e->approved_by)
                  <div class="text-[11px] text-slate-500 mt-1">
                    by {{ $e->approver?->name ?? '—' }}
                    @if($e->approved_at) • {{ $e->approved_at->format('Y-m-d H:i') }} @endif
                  </div>
                @endif
              </td>
              <td class="py-2 border-b border-slate-100">
                <div class="flex items-center justify-end gap-2">
                  <a href="{{ route('admin.hr-entries.edit',$e) }}"
                     class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-transparent bg-transparent text-slate-600 hover:bg-slate-100">
                    Edit
                  </a>

                  {{-- Approve/Reject --}}
                  @can('approve', $e)
                    @if(($e->status ?? 'pending') !== 'approved')
                      <form method="POST" action="{{ route('admin.hr-entries.approve',$e) }}">
                        @csrf
                        <button
                          class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700"
                          onclick="return confirm('Setujui entry ini?')">
                          Approve
                        </button>
                      </form>
                    @endif
                  @endcan

                  @can('reject', $e)
                    @if(($e->status ?? 'pending') !== 'rejected')
                      <form method="POST" action="{{ route('admin.hr-entries.reject',$e) }}">
                        @csrf
                        <button
                          class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-rose-600 bg-rose-600 text-white hover:bg-rose-700"
                          onclick="return confirm('Tolak entry ini?')">
                          Reject
                        </button>
                      </form>
                    @endif
                  @endcan

                  {{-- Delete --}}
                  <form method="POST" action="{{ route('admin.hr-entries.destroy',$e) }}"
                        onsubmit="return confirm('Hapus entry ini?')">
                    @csrf @method('DELETE')
                    <button
                      class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-slate-200 bg-white hover:bg-slate-50">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td class="py-6 border-b border-slate-100 text-center text-slate-500" colspan="8">
                Belum ada data. <a href="{{ route('admin.hr-entries.create') }}" class="text-emerald-700 underline decoration-dotted">Tambah entry</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-3 md:p-4 border-t border-slate-100">
      {{ $entries->links() }}
    </div>
  </div>
</div>
@endsection
