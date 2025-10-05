@extends('layouts.app')

@section('title','Daftar Assets')

@section('content')
<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] text-white flex items-center justify-between">
    <div class="space-y-1">
      <h1 class="text-xl font-bold">📦 Daftar Assets</h1>
      <p class="text-xs text-white/80">Kelola aset unit, kendaraan, IT, atau infrastruktur per site.</p>

      {{-- Site aktif --}}
      @if(!empty($currentSite))
        <div class="mt-1 inline-flex items-center gap-2 text-[11px]">
          <span class="px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">
            Site: <strong class="ml-1">{{ $currentSite->code }}</strong>
          </span>
          <a href="{{ route('sites.select') }}"
             class="underline decoration-white/50 hover:decoration-white">ganti</a>
        </div>
      @endif
    </div>

    <div class="flex items-center gap-2">
      @if (Route::has('admin.assets.create'))
      <a href="{{ route('admin.assets.create') }}"
         class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/10 text-white text-sm font-medium ring-1 ring-white/30 hover:bg-white/20">
        ➕ Tambah Asset
      </a>
      @endif
    </div>
  </div>

  {{-- FLASH / ALERTS --}}
  @if(session('status') || session('success'))
    <div class="px-6 py-3 bg-emerald-50 text-emerald-700 text-sm border-b border-emerald-200">
      {{ session('status') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-6 py-3 bg-red-50 text-red-700 text-sm border-b border-red-200">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- FILTER & SEARCH --}}
  <div class="px-6 py-3 bg-slate-50 border-b flex items-center justify-between gap-4">
    <form method="GET" action="{{ route('admin.assets.index') }}" class="flex gap-2 flex-1">
      <input type="text" name="q" value="{{ request('q') }}"
             placeholder="Cari nama/kode/serial/plate…"
             class="flex-1 rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
      <button type="submit"
              class="px-3 py-1.5 rounded-lg bg-teal-600 text-white text-sm hover:bg-teal-700">
        Cari
      </button>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm divide-y divide-slate-200">
      <thead class="bg-slate-50 text-slate-700">
        <tr>
          <th class="px-4 py-2 text-left font-medium">Kode</th>
          <th class="px-4 py-2 text-left font-medium">Nama</th>
          <th class="px-4 py-2 text-left font-medium">Kategori</th>
          <th class="px-4 py-2 text-left font-medium">Cost Center</th>
          <th class="px-4 py-2 text-left font-medium">Status</th>
          <th class="px-4 py-2 text-left font-medium">Site</th>
          <th class="px-4 py-2 text-right font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($assets as $a)
          <tr>
            <td class="px-4 py-2 font-mono text-slate-700">{{ $a->code ?? '-' }}</td>
            <td class="px-4 py-2">
              <div class="text-slate-800 font-semibold">{{ $a->name }}</div>
              <div class="text-[11px] text-slate-400">
                @if($a->serial_no) SN: {{ $a->serial_no }} @endif
                @if($a->plate_no) <span class="ml-2">Plate: {{ $a->plate_no }}</span> @endif
              </div>
            </td>
            <td class="px-4 py-2">
              <div>{{ $a->category?->name ?? '-' }}</div>
              @if($a->category?->code)
                <div class="text-[11px] text-slate-400">{{ $a->category->code }}</div>
              @endif
            </td>
            <td class="px-4 py-2">
              <div>{{ $a->costCenter?->name ?? '-' }}</div>
              @if($a->costCenter?->code)
                <div class="text-[11px] text-slate-400">{{ $a->costCenter->code }}</div>
              @endif
            </td>
            <td class="px-4 py-2">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium
                @class([
                  'bg-emerald-100 text-emerald-700' => $a->status === 'active',
                  'bg-yellow-100 text-yellow-700' => $a->status === 'repair',
                  'bg-red-100 text-red-700' => in_array($a->status,['inactive','sold','disposed']),
                  'bg-slate-100 text-slate-600' => !in_array($a->status,['active','repair','inactive','sold','disposed']),
                ])">
                {{ ucfirst($a->status ?? 'unknown') }}
              </span>
            </td>
            <td class="px-4 py-2 text-slate-600">{{ $a->site?->code ?? '-' }}</td>
            <td class="px-4 py-2 text-right">
              @if (Route::has('admin.assets.edit'))
              <a href="{{ route('admin.assets.edit',$a) }}"
                 class="text-teal-600 hover:text-teal-800 text-xs font-medium">Edit</a>
              @endif>
              @if (Route::has('admin.assets.destroy'))
              <form action="{{ route('admin.assets.destroy',$a) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Hapus asset ini?')"
                        class="text-red-600 hover:text-red-800 text-xs font-medium ml-2">
                  Hapus
                </button>
              </form>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-4 py-10 text-center">
              <div class="text-slate-500">Belum ada asset di site ini.</div>
              <div class="mt-2 text-[13px] text-slate-400">
                Pastikan <em>Master Data</em> <strong>Asset Categories</strong> dan <strong>Cost Centers</strong> tersedia.
                @if (Route::has('admin.master.index'))
                  <div class="mt-2">
                    <a href="{{ route('admin.master.index',['entity'=>'asset_categories']) }}"
                       class="underline text-teal-600 hover:text-teal-700 mr-3">Buka Asset Categories</a>
                    <a href="{{ route('admin.master.index',['entity'=>'cost_centers']) }}"
                       class="underline text-teal-600 hover:text-teal-700">Buka Cost Centers</a>
                  </div>
                @endif
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="px-6 py-3 border-t bg-slate-50">
    {{ $assets->links() }}
  </div>
</div>
@endsection
