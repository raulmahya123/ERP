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

<style>[x-cloak]{display:none}</style>

<div x-data="{ showModal:false, submitting:false }" class="space-y-6">

  {{-- ALERTS --}}  @if ($errors->any())
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 ring-1 ring-red-200">
      <div class="text-sm font-semibold mb-1">Gagal menyimpan:</div>
      <ul class="text-sm list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- HERO: Hijau-Emas-Biru --}}
  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-7 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
        <div class="flex items-start gap-4">
          <div class="h-12 w-12 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-6 w-6 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">🏠 Dashboard</h1>
            <p class="text-white/90 text-sm mt-1">Kelola aset lebih cepat. Buat dulu, assign site nanti. Seragam warna hijau-emas-biru.</p>
          </div>
        </div>

        @if($isGMorMgr)
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('sites.select') }}"
             class="px-4 py-2 rounded-xl bg-white/10 text-white ring-1 ring-white/30 hover:bg-white/15 text-sm font-medium transition">
            Ganti Site
          </a>
          <button @click="showModal=true"
                  class="px-4 py-2 rounded-xl bg-emerald-500 text-white font-semibold hover:bg-emerald-600 text-sm shadow-md ring-1 ring-emerald-700/20 transition">
            + Create Asset
          </button>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- KPI STRIP (opsional, serumpun) --}}
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl ring-1 ring-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-4">
      <div class="text-xs text-emerald-700/80 font-medium">Total Asset</div>
      <div class="mt-1 text-2xl font-extrabold text-emerald-900">{{ number_format($stats['total_assets'] ?? 0) }}</div>
    </div>
    <div class="rounded-2xl ring-1 ring-amber-200 bg-gradient-to-br from-amber-50 to-white p-4">
      <div class="text-xs text-amber-700/90 font-medium">Belum ada Site</div>
      <div class="mt-1 text-2xl font-extrabold text-amber-900">{{ number_format($stats['no_site'] ?? 0) }}</div>
    </div>
    <div class="rounded-2xl ring-1 ring-sky-200 bg-gradient-to-br from-sky-50 to-white p-4">
      <div class="text-xs text-sky-700/90 font-medium">Active</div>
      <div class="mt-1 text-2xl font-extrabold text-sky-900">{{ number_format($stats['active'] ?? 0) }}</div>
    </div>
    <div class="rounded-2xl ring-1 ring-slate-200 bg-white p-4">
      <div class="text-xs text-slate-600 font-medium">Retired</div>
      <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($stats['retired'] ?? 0) }}</div>
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
      <a href="{{ route('admin.assets.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">
        Lihat semua →
      </a>
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
                class="mt-3 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 text-sm shadow-md ring-1 ring-emerald-700/20">
          + Tambah Asset
        </button>
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
              <th class="py-2 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentAssets as $a)
            <tr class="border-t hover:bg-emerald-50/40">
              <td class="py-3 px-4">
                <div class="font-semibold text-slate-800">{{ $a->name }}</div>
                <div class="text-xs text-slate-500">#{{ Str::limit($a->id, 8, '') }}</div>
              </td>
              <td class="py-3 px-4">{{ optional($a->category)->name ?? '—' }}</td>
              <td class="py-3 px-4">
                @if($a->site_id)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>{{ $a->site->code ?? 'SITE' }}
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-lg bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                    <span class="size-1.5 rounded-full bg-amber-500"></span>Belum ada site
                  </span>
                @endif
              </td>
              <td class="py-3 px-4">
                <div>{{ $a->acq_date?->format('d M Y') ?: '—' }}</div>
                <div class="text-xs text-slate-500">Rp {{ number_format((float)($a->acq_cost ?? 0), 2, ',', '.') }}</div>
              </td>
              <td class="py-3 px-4">
                @if($isGMorMgr)
                <div x-data="{open:false}" class="relative flex items-center justify-center">
                  <button @click="open=!open" @keydown.escape.window="open=false" type="button"
                          class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold
                                 bg-emerald-600 text-white shadow hover:bg-emerald-700 ring-1 ring-emerald-700/20 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6h.01M12 12h.01M12 18h.01"/>
                    </svg>
                    Actions
                    <svg class="h-4 w-4 -mr-0.5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                    </svg>
                  </button>

                  <div x-cloak x-show="open" @click.outside="open=false" x-transition.origin.top.right
                       class="absolute right-0 top-9 w-44 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 overflow-hidden z-20">
                    <a href="{{ route('admin.assets.show', $a->id) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-emerald-50/60">
                      <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-4.55a1.5 1.5 0 10-2.12-2.12L12.88 7.88M5 19l4.55-4.55m0 0L19 5m-9.45 9.45L10 15l-5 5"/>
                      </svg>
                      Detail
                    </a>
                    <a href="{{ route('admin.assets.edit', $a->id) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-sky-50/70">
                      <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                      </svg>
                      Edit
                    </a>
                    <a href="{{ route('admin.assets.assignments.index', $a->id) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-amber-50/70">
                      <svg class="h-4 w-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4 4 4M12 3v10m-7 8h14a2 2 0 002-2v-5a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2z"/>
                      </svg>
                      Transfer
                    </a>
                  </div>
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
      <div class="px-6 py-3 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700 text-white rounded-t-2xl flex justify-between items-center">
        <h3 class="font-semibold">Tambah Asset</h3>
        <button @click="showModal=false" class="text-white/80 hover:text-white">&times;</button>
      </div>

      <form method="POST" action="{{ route('dashboard.assets.quick-store') }}" class="p-6 space-y-4" @submit="submitting=true">
        @csrf

        {{-- Kode & Nama --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Kode</label>
            <input name="code" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Nama <span class="text-red-600">*</span></label>
            <input name="name" required class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
        </div>

        {{-- Kategori & Cost Center --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Kategori Asset</label>
            <select name="asset_category_id" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
              <option value="">— pilih —</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Cost Center</label>
            <select name="cost_center_id" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
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
            <input name="brand" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Model</label>
            <input name="model" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
        </div>

        {{-- Serial No & Plate/Unit No --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Serial No</label>
            <input name="serial_no" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Plate No / Unit No</label>
            <input name="plate_no" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
        </div>

        {{-- Status & Tanggal Commissioning --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="retired">Retired</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Tanggal Commissioning</label>
            <input type="date" name="commissioned_at" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
        </div>

        {{-- Acquisition Cost & Date --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Acquisition Cost</label>
            <input type="number" step="0.01" name="acq_cost" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Acquisition Date</label>
            <input type="date" name="acq_date" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
          </div>
        </div>

        {{-- Lokasi --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Lokasi</label>
          <input name="location" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600">
        </div>

        {{-- Extra --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Extra (JSON / text)</label>
          <textarea name="extra[notes]" rows="3" class="mt-1 w-full rounded-xl border-slate-300 focus:ring-emerald-600 focus:border-emerald-600"></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="showModal=false" class="px-4 py-2 text-sm rounded-xl bg-white ring-1 ring-slate-200 hover:bg-slate-50">
            Batal
          </button>
          <button :disabled="submitting"
                  class="px-4 py-2 text-sm rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 disabled:opacity-60 shadow-md ring-1 ring-emerald-700/20">
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
  @endif

</div>
@endsection
