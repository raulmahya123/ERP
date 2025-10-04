{{-- resources/views/admin/site_configs/index.blade.php --}}
@extends('layouts.app')
@section('title','Konfigurasi Site')

@section('header')
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Konfigurasi Site</h1>
      <p class="text-slate-500 text-sm">List konfigurasi per site & komoditas.</p>
    </div>
    <a href="{{ route('admin.site_config.create', ['site' => $selectedSiteId]) }}"
       class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm">
      + Tambah Konfigurasi
    </a>
  </div>
@endsection

@section('content')
  @if (session('success'))
    <div class="mb-4 p-3 bg-emerald-50 text-emerald-700 rounded">{{ session('success') }}</div>
  @endif

  <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
    <div>
      <label class="block text-xs text-slate-500 mb-1">Filter Site</label>
      <select name="site" class="w-full border rounded px-2 py-1.5">
        <option value="">— Semua Site —</option>
        @foreach ($sites as $s)
          <option value="{{ $s->id }}" @selected($selectedSiteId === $s->id)>
            {{ $s->code }} — {{ $s->name }}
          </option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Filter Komoditas</label>
      <select name="commodity" class="w-full border rounded px-2 py-1.5">
        <option value="">— Semua Komoditas —</option>
        @foreach ($commodities as $c)
          <option value="{{ $c->id }}" @selected($selectedCommodityId === $c->id)>
            {{ $c->code }} — {{ $c->name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="flex items-end">
      <button class="px-3 py-2 bg-slate-800 text-white rounded">Terapkan</button>
      <a href="{{ route('admin.site_config.index') }}" class="ml-2 px-3 py-2 border rounded">Reset</a>
    </div>
  </form>

  <div class="overflow-x-auto bg-white border rounded">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50">
        <tr class="text-left">
          <th class="px-3 py-2">Site</th>
          <th class="px-3 py-2">Komoditas</th>
          <th class="px-3 py-2">HBA</th>
          <th class="px-3 py-2">Ni Grade Min</th>
          <th class="px-3 py-2">Assay Method</th>
          <th class="px-3 py-2">Shift Roster</th>
          <th class="px-3 py-2 w-32">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($configs as $cfg)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $cfg->site?->code }} — {{ $cfg->site?->name }}</td>
            <td class="px-3 py-2">{{ $cfg->commodity?->code }} — {{ $cfg->commodity?->name }}</td>
            <td class="px-3 py-2">{{ data_get($cfg->params,'hba') ?? '—' }}</td>
            <td class="px-3 py-2">{{ data_get($cfg->params,'ni_grade_min') ?? '—' }}</td>
            <td class="px-3 py-2">{{ data_get($cfg->params,'assay_method') ?? '—' }}</td>
            <td class="px-3 py-2">
              @php $roster = data_get($cfg->params,'shift_roster', []); @endphp
              @if ($roster && is_array($roster))
                <span title="{{ implode(', ', $roster) }}">
                  {{ implode(', ', array_slice($roster,0,3)) }}{{ count($roster)>3?'…':'' }}
                </span>
              @else
                —
              @endif
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                <a href="{{ route('admin.site_config.edit', $cfg) }}"
                   class="px-2 py-1 text-xs rounded bg-amber-500 text-white">Edit</a>
                <form method="POST" action="{{ route('admin.site_config.destroy', $cfg) }}"
                      onsubmit="return confirm('Hapus konfigurasi ini?')">
                  @csrf @method('DELETE')
                  <button class="px-2 py-1 text-xs rounded bg-rose-600 text-white">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td class="px-3 py-6 text-center text-slate-500" colspan="7">Belum ada konfigurasi.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $configs->links() }}</div>
@endsection
