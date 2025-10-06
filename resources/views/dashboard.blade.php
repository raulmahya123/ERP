{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title','Dashboard')

@section('content')
@php
  use Illuminate\Support\Str;

  // Deteksi role GM/Manager tanpa @hasrole
  $r   = auth()->user()->role ?? null;
  $raw = is_object($r) ? ($r->key ?? $r->slug ?? $r->name ?? '') : (is_string($r) ? $r : '');
  $norm = Str::of($raw)->lower()->replace(['_', '-'], ' ')->squish()->toString();
  $map  = ['gm'=>'gm','general manager'=>'gm','generalmanager'=>'gm','manager'=>'manager','mgr'=>'manager'];
  $roleKey   = $map[$norm] ?? $norm;
  $isGMorMgr = in_array($roleKey, ['gm','manager'], true);
@endphp

<div x-data="{ showModal:false, submitting:false }" class="space-y-6">

  {{-- ALERTS --}}
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 text-emerald-900 px-4 py-3 ring-1 ring-emerald-200">
      <div class="text-sm font-medium">{{ session('success') }}</div>
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 ring-1 ring-red-200">
      <div class="text-sm font-semibold mb-1">Gagal menyimpan:</div>
      <ul class="text-sm list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- HERO --}}
  <div class="relative overflow-hidden rounded-3xl ring-1 ring-slate-200 shadow-sm">
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 via-indigo-600 to-amber-500"></div>
    <div class="relative p-6 sm:p-8 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">🏠 Dashboard</h1>
        <p class="text-white/90 text-sm mt-1">Kelola asetmu lebih cepat. Buat asset tanpa site, lalu transfer saat siap.</p>
      </div>
      @if($isGMorMgr)
      <div class="flex gap-2">
        <a href="{{ route('sites.select') }}"
           class="px-4 py-2 rounded-xl bg-white/10 text-white ring-1 ring-white/30 hover:bg-white/15 text-sm font-medium transition">
          Ganti Site
        </a>
        <button @click="showModal=true"
                class="px-4 py-2 rounded-xl bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 text-sm shadow transition">
          + Create Asset
        </button>
      </div>
      @endif
    </div>
  </div>

  {{-- LIST ASSET TERBARU --}}
  <div class="rounded-3xl overflow-hidden bg-white shadow ring-1 ring-slate-200">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
      <h2 class="font-semibold text-slate-800 text-lg flex items-center gap-2">
        <svg class="w-5 h-5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.5" d="M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
        Asset Terbaru
      </h2>
      @if($isGMorMgr)
      <a href="{{ route('admin.assets.index') }}" class="text-sm text-indigo-700 hover:text-indigo-900 font-medium">Lihat semua →</a>
      @endif
    </div>

    @if($recentAssets->isEmpty())
      <div class="p-10 text-center text-slate-600">
        <div class="mx-auto w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center">
          <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.5" d="M12 5v14m7-7H5"/></svg>
        </div>
        <p class="mt-3 font-medium text-slate-700">Belum ada asset</p>
        @if($isGMorMgr)
        <button @click="showModal=true"
                class="mt-3 px-4 py-2 rounded-xl bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 text-sm">+ Tambah Asset</button>
        @endif
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50">
            <tr class="text-left text-slate-600 border-b border-slate-200">
              <th class="py-2 px-4">Nama</th>
              <th class="py-2 px-4">Kategori</th>
              <th class="py-2 px-4">Site</th>
              <th class="py-2 px-4">Perolehan</th>
              <th class="py-2 px-4">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentAssets as $a)
            <tr class="border-t hover:bg-slate-50">
              <td class="py-3 px-4">
                <div class="font-medium text-slate-800">{{ $a->name }}</div>
                <div class="text-xs text-slate-500">#{{ Str::limit($a->id, 8, '') }}</div>
              </td>
              <td class="py-3 px-4">{{ optional($a->category)->name ?? '—' }}</td>
              <td class="py-3 px-4">
                @if($a->site_id)
                  <span class="inline-block px-2 py-0.5 text-xs rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">{{ $a->site->code ?? 'SITE' }}</span>
                @else
                  <span class="inline-block px-2 py-0.5 text-xs rounded-lg bg-amber-50 text-amber-700 ring-1 ring-amber-200">Belum ada site</span>
                @endif
              </td>
              <td class="py-3 px-4">
                <div>{{ $a->acq_date?->format('d M Y') ?: '—' }}</div>
                <div class="text-xs text-slate-500">Rp {{ number_format((float)($a->acq_cost ?? 0), 2, ',', '.') }}</div>
              </td>
              <td class="py-3 px-4">
                @if($isGMorMgr)
                <div class="flex gap-2">
                  <a href="{{ route('admin.assets.edit', $a->id) }}"
                     class="px-2 py-1 rounded-lg bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 text-xs">Edit</a>
                  <a href="{{ route('admin.assets.assignments.index', $a->id) }}"
                     class="px-2 py-1 rounded-lg bg-indigo-700 text-white hover:bg-indigo-800 text-xs">Transfer</a>
                </div>
                @else
                  <span class="text-slate-400 text-xs">—</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  {{-- MODAL QUICK CREATE --}}
  @if($isGMorMgr)
  <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="showModal=false"></div>
    <div class="relative w-full sm:max-w-2xl bg-white rounded-2xl shadow-lg ring-1 ring-slate-200 m-4 sm:m-0">
      <div class="px-6 py-3 bg-gradient-to-r from-indigo-700 via-indigo-600 to-amber-500 text-white rounded-t-2xl flex justify-between items-center">
        <h3 class="font-semibold">Tambah Asset</h3>
        <button @click="showModal=false" class="text-white/80 hover:text-white">&times;</button>
      </div>

      <form method="POST" action="{{ route('dashboard.assets.quick-store') }}" class="p-6 space-y-4" @submit="submitting=true">
        @csrf

        {{-- Kode & Nama --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Kode</label>
            <input name="code" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Nama <span class="text-red-600">*</span></label>
            <input name="name" required class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
          </div>
        </div>

        {{-- Kategori & Cost Center --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Kategori Asset</label>
            <select name="asset_category_id" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
              <option value="">— pilih —</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Cost Center</label>
            <select name="cost_center_id" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
              <option value="">— pilih —</option>
              @foreach(($costCenters ?? []) as $cc)
                <option value="{{ $cc->id }}">{{ $cc->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Brand & Model --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Brand</label>
            <input name="brand" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Model</label>
            <input name="model" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
          </div>
        </div>

        {{-- Serial No & Plate/Unit No --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Serial No</label>
            <input name="serial_no" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Plate No / Unit No</label>
            <input name="plate_no" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus-border-indigo-600">
          </div>
        </div>

        {{-- Status & Tanggal Commissioning --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="retired">Retired</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Tanggal Commissioning</label>
            <input type="date" name="commissioned_at" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
          </div>
        </div>

        {{-- Acquisition Cost & Date --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Acquisition Cost</label>
            <input type="number" step="0.01" name="acq_cost" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Acquisition Date</label>
            <input type="date" name="acq_date" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
          </div>
        </div>

        {{-- Lokasi --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Lokasi</label>
          <input name="location" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600">
        </div>

        {{-- Extra --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Extra (JSON / text)</label>
          <textarea name="extra[notes]" rows="3" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-indigo-600 focus:border-indigo-600"></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="showModal=false" class="px-4 py-2 text-sm rounded-xl bg-white ring-1 ring-slate-200 hover:bg-slate-50">Batal</button>
          <button :disabled="submitting" class="px-4 py-2 text-sm rounded-xl bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 disabled:opacity-60">Simpan</button>
        </div>
      </form>
    </div>
  </div>
  @endif

</div>
@endsection
