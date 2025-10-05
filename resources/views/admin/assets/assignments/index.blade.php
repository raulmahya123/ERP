@extends('layouts.app')

@section('title','Riwayat Penempatan Aset')

@section('content')
<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] text-white flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold">📜 Riwayat Penempatan</h1>
      <p class="text-xs text-white/80">Catat dan pantau perpindahan aset antar site / user.</p>

      {{-- Asset summary --}}
      <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
        <span class="px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">
          Aset: <strong class="ml-1">{{ $asset->name }}</strong>
        </span>
        @if($asset->code)
          <span class="px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">Kode: {{ $asset->code }}</span>
        @endif
        @if($asset->site?->code)
          <span class="px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">Site Saat Ini: {{ $asset->site?->code }}</span>
        @endif
        @if($asset->status)
          <span class="px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">Status: {{ ucfirst($asset->status) }}</span>
        @endif
      </div>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.assets.index') }}"
         class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/10 text-white text-sm font-medium ring-1 ring-white/30 hover:bg-white/20">
        ← Kembali
      </a>
      @if (Route::has('admin.assets.edit'))
        <a href="{{ route('admin.assets.edit', $asset) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/10 text-white text-sm font-medium ring-1 ring-white/30 hover:bg-white/20">
          ✏️ Edit Aset
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

  {{-- FORM TAMBAH PENEMPATAN / TRANSFER --}}
  <div class="px-6 py-5 bg-slate-50 border-b">
    <h2 class="font-semibold text-slate-700 mb-3">Tambah Penempatan / Transfer</h2>

    <form id="assign-form" method="POST" action="{{ route('admin.assets.assignments.store', $asset) }}" class="grid gap-3 sm:grid-cols-2">
      @csrf

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-slate-700">Site Tujuan</label>
        <select name="to_site_id" class="mt-1 w-full rounded-xl border-slate-300" required>
          <option value="">— Pilih site —</option>
          @foreach(\App\Models\Site::orderBy('name')->get() as $s)
            <option value="{{ $s->id }}" @selected(old('to_site_id')===$s->id)>{{ $s->name }} ({{ $s->code }})</option>
          @endforeach
        </select>
        @error('to_site_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-slate-700">User Penerima (opsional)</label>
        <select name="to_user_id" class="mt-1 w-full rounded-xl border-slate-300">
          <option value="">— (kosong) —</option>
          @foreach(\App\Models\User::orderBy('name')->get() as $u)
            <option value="{{ $u->id }}" @selected(old('to_user_id')===$u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
        @error('to_user_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-slate-700">Tanggal Efektif</label>
        <input type="date" name="assigned_at" value="{{ old('assigned_at', now()->toDateString()) }}" class="mt-1 w-full rounded-xl border-slate-300">
        @error('assigned_at') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Catatan</label>
        <textarea name="note" rows="2" class="mt-1 w-full rounded-xl border-slate-300" placeholder="Mis. transfer dari HO ke Site DBK">{{ old('note') }}</textarea>
        @error('note') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="sm:col-span-2">
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[--navy] text-white">
          Simpan
        </button>
      </div>
    </form>
  </div>

  {{-- TABEL RIWAYAT --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm divide-y divide-slate-200">
      <thead class="bg-slate-50 text-slate-700">
        <tr>
          <th class="px-4 py-2 text-left font-medium">Tanggal</th>
          <th class="px-4 py-2 text-left font-medium">Dari → Ke (Site)</th>
          <th class="px-4 py-2 text-left font-medium">User (Dari → Ke)</th>
          <th class="px-4 py-2 text-left font-medium">Dibuat Oleh</th>
          <th class="px-4 py-2 text-left font-medium">Catatan</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($assignments as $a)
          <tr>
            <td class="px-4 py-2 text-sm">{{ optional($a->assigned_at)->format('d M Y') ?: '—' }}</td>
            <td class="px-4 py-2 text-sm">
              {{ $a->fromSite?->code ?? '—' }} → <span class="font-medium">{{ $a->toSite?->code ?? '—' }}</span>
            </td>
            <td class="px-4 py-2 text-sm">
              {{ $a->fromUser?->name ?? '—' }} → <span class="font-medium">{{ $a->toUser?->name ?? '—' }}</span>
            </td>
            <td class="px-4 py-2 text-sm">{{ $a->creator?->name ?? '—' }}</td>
            <td class="px-4 py-2 text-sm">{{ $a->note ?: '—' }}</td>
            <td class="px-4 py-2 text-right">
              <form method="POST" action="{{ route('admin.assets.assignments.destroy', [$asset, $a]) }}" onsubmit="return confirm('Hapus riwayat ini?');">
                @csrf @method('DELETE')
                <button class="text-sm text-red-600 hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada riwayat.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="px-6 py-3 border-t bg-slate-50">
    {{ $assignments->links() }}
  </div>

</div>
@endsection
