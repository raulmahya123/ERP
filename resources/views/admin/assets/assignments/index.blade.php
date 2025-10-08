{{-- resources/views/admin/assets/assignments.blade.php --}}
@extends('layouts.app')

@section('title','Riwayat Penempatan Aset')

@section('content')
<style>[x-cloak]{display:none}</style>

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- ===== HEADER (serumpun hijau–emas–biru) ===== --}}
  <div class="relative px-6 py-5 text-white bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700 flex items-center justify-between">
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(70%_70%_at_10%_10%,_#fff_0%,_transparent_60%)]"></div>

    <div class="relative">
      <h1 class="text-xl font-extrabold tracking-tight">📜 Riwayat Penempatan</h1>
      <p class="text-xs text-white/85">Catat dan pantau perpindahan aset antar site / user.</p>

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

    <div class="relative flex items-center gap-2">
      <a href="{{ route('admin.assets.index') }}"
         class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
        ← Kembali
      </a>
      @if (Route::has('admin.assets.edit'))
        <a href="{{ route('admin.assets.edit', $asset) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-400 text-slate-900 text-sm font-semibold ring-1 ring-amber-300/40 hover:bg-amber-300 transition">
          ✏️ Edit Aset
        </a>
      @endif
    </div>
  </div>

  {{-- ===== FLASH / ALERTS ===== --}}
  @if(session('status') || session('success'))
    <div class="px-6 py-3 bg-emerald-50 text-emerald-900 text-sm ring-1 ring-emerald-200">
      {{ session('status') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-6 py-3 bg-red-50 text-red-700 text-sm ring-1 ring-red-200">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- ===== FORM TAMBAH PENEMPATAN / TRANSFER ===== --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50 border-b border-slate-200">
    <h2 class="font-semibold text-slate-800 mb-3">Tambah Penempatan / Transfer</h2>

    <form id="assign-form" method="POST" action="{{ route('admin.assets.assignments.store', $asset) }}" class="grid gap-4 sm:grid-cols-2">
      @csrf

      {{-- Site Tujuan --}}
      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-slate-700">Site Tujuan <span class="text-red-600">*</span></label>
        <select name="to_site_id"
                class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
                required>
          <option value="">— Pilih site —</option>
          @foreach(\App\Models\Site::orderBy('name')->get() as $s)
            <option value="{{ $s->id }}" @selected(old('to_site_id')==$s->id)>{{ $s->name }} ({{ $s->code }})</option>
          @endforeach
        </select>
        @error('to_site_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- User Penerima --}}
      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-slate-700">User Penerima (opsional)</label>
        <select name="to_user_id"
                class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
          <option value="">— (kosong) —</option>
          @foreach(\App\Models\User::orderBy('name')->get() as $u)
            <option value="{{ $u->id }}" @selected(old('to_user_id')==$u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
        @error('to_user_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Tanggal Efektif --}}
      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-slate-700">Tanggal Efektif</label>
        <input type="date" name="assigned_at"
               value="{{ old('assigned_at', now()->toDateString()) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        @error('assigned_at') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Catatan --}}
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Catatan</label>
        <textarea name="note" rows="2"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
                  placeholder="Mis. transfer dari HO ke Site DBK">{{ old('note') }}</textarea>
        @error('note') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Submit --}}
      <div class="sm:col-span-2 pt-1">
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-white
                       bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700
                       hover:from-emerald-700 hover:to-sky-800 shadow">
          Simpan
        </button>
      </div>
    </form>
  </div>

  {{-- ===== TABEL RIWAYAT ===== --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
        <tr>
          <th class="px-4 py-3 text-left font-medium">Tanggal</th>
          <th class="px-4 py-3 text-left font-medium">Dari → Ke (Site)</th>
          <th class="px-4 py-3 text-left font-medium">User (Dari → Ke)</th>
          <th class="px-4 py-3 text-left font-medium">Dibuat Oleh</th>
          <th class="px-4 py-3 text-left font-medium">Catatan</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="[&>tr:nth-child(even)]:bg-slate-50/40">
        @forelse($assignments as $a)
          <tr class="border-b border-slate-100 hover:bg-emerald-50/40 transition">
            <td class="px-4 py-2">{{ optional($a->assigned_at)->format('d M Y') ?: '—' }}</td>
            <td class="px-4 py-2">
              {{ $a->fromSite?->code ?? '—' }} → <span class="font-semibold">{{ $a->toSite?->code ?? '—' }}</span>
            </td>
            <td class="px-4 py-2">
              {{ $a->fromUser?->name ?? '—' }} → <span class="font-semibold">{{ $a->toUser?->name ?? '—' }}</span>
            </td>
            <td class="px-4 py-2">{{ $a->creator?->name ?? '—' }}</td>
            <td class="px-4 py-2">{{ $a->note ?: '—' }}</td>
            <td class="px-4 py-2 text-right">
              <form method="POST" action="{{ route('admin.assets.assignments.destroy', [$asset, $a]) }}" onsubmit="return confirm('Hapus riwayat ini?');">
                @csrf @method('DELETE')
                <button class="inline-flex items-center gap-1 text-red-600 hover:underline">
                  Hapus
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-10 text-center text-slate-600">
              Belum ada riwayat.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- ===== PAGINATION ===== --}}
  <div class="px-6 py-3 border-t border-slate-200 bg-slate-50">
    {{ $assignments->links() }}
  </div>

</div>
@endsection
