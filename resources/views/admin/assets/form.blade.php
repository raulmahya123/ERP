{{-- resources/views/admin/assets/form.blade.php --}}
@extends('layouts.app')

@section('title', $asset->exists ? 'Edit Asset' : 'Tambah Asset')

@section('content')
<style>[x-cloak]{display:none}</style>

<div class="max-w-4xl mx-auto bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- ===== HERO (serumpun hijau–emas–biru) ===== --}}
  <div class="relative px-6 py-5 text-white bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(70%_70%_at_10%_10%,_#fff_0%,_transparent_60%)]"></div>

    <div class="relative flex items-start justify-between">
      <div>
        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">
          {{ $asset->exists ? '✏️ Edit Asset' : '➕ Tambah Asset' }}
        </h1>
        <p class="text-white/85 text-sm">Isi detail asset untuk site aktif.</p>

        @if(!empty($currentSite))
          <div class="mt-2 text-[11px]">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">
              <i class="size-1.5 rounded-full bg-amber-300"></i>
              Site: <strong class="ml-1">{{ $currentSite->code }}</strong>
            </span>
            <a href="{{ route('sites.select') }}" class="underline decoration-white/50 hover:decoration-white ml-2">ganti</a>
          </div>
        @endif
      </div>

      <a href="{{ route('admin.assets.index') }}"
         class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 hover:bg-white/15 transition">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- ===== FLASH & ERRORS ===== --}}
  @if(session('status') || session('success'))
    <div class="px-6 py-3 bg-emerald-50 text-emerald-900 text-sm ring-1 ring-emerald-200">
      {{ session('status') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-6 py-3 bg-red-50 text-red-700 text-sm ring-1 ring-red-200">
      <ul class="list-disc list-inside space-y-0.5">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- ===== FORM ===== --}}
  <form method="POST"
        action="{{ $asset->exists ? route('admin.assets.update',$asset) : route('admin.assets.store') }}"
        class="p-6 space-y-6">
    @csrf
    @if($asset->exists) @method('PUT') @endif

    {{-- Section: Identitas --}}
    <div>
      <div class="text-xs font-semibold text-slate-500 mb-2">Identitas</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Kode</label>
          <input type="text" name="code" value="{{ old('code',$asset->code) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('code') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Nama <span class="text-red-600">*</span></label>
          <input type="text" name="name" value="{{ old('name',$asset->name) }}" required
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
      </div>
    </div>

    {{-- Section: Kategori & Cost Center --}}
    <div>
      <div class="text-xs font-semibold text-slate-500 mb-2">Pengelompokan</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-slate-700">Kategori Asset</label>
            @if($categories->isEmpty() && Route::has('admin.master.index'))
              <a href="{{ route('admin.master.index',['entity'=>'asset_categories']) }}" class="text-[11px] text-emerald-700 underline">isi master</a>
            @endif
          </div>
          <select name="asset_category_id"
                  class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
            <option value="">— pilih —</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected(old('asset_category_id',$asset->asset_category_id)==$cat->id)>
                {{ $cat->name }}{{ $cat->code ? " ({$cat->code})" : '' }}
              </option>
            @endforeach
          </select>
          @error('asset_category_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-slate-700">Cost Center</label>
            @if($costCenters->isEmpty() && Route::has('admin.master.index'))
              <a href="{{ route('admin.master.index',['entity'=>'cost_centers']) }}" class="text-[11px] text-emerald-700 underline">isi master</a>
            @endif
          </div>
          <select name="cost_center_id"
                  class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
            <option value="">— pilih —</option>
            @foreach($costCenters as $cc)
              <option value="{{ $cc->id }}" @selected(old('cost_center_id',$asset->cost_center_id)==$cc->id)>
                {{ $cc->name }}{{ $cc->code ? " ({$cc->code})" : '' }}
              </option>
            @endforeach
          </select>
          @error('cost_center_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
      </div>
    </div>

    {{-- Section: Spesifikasi --}}
    <div>
      <div class="text-xs font-semibold text-slate-500 mb-2">Spesifikasi</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Brand</label>
          <input type="text" name="brand" value="{{ old('brand',$asset->brand) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('brand') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Model</label>
          <input type="text" name="model" value="{{ old('model',$asset->model) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('model') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Serial No</label>
          <input type="text" name="serial_no" value="{{ old('serial_no',$asset->serial_no) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('serial_no') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Plate No / Unit No</label>
          <input type="text" name="plate_no" value="{{ old('plate_no',$asset->plate_no) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('plate_no') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
      </div>
    </div>

    {{-- Section: Status & Commissioning --}}
    <div>
      <div class="text-xs font-semibold text-slate-500 mb-2">Status Operasional</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Status</label>
          <select name="status"
                  class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
            @foreach(['active'=>'Active','inactive'=>'Inactive','repair'=>'Repair','sold'=>'Sold','disposed'=>'Disposed'] as $val=>$label)
              <option value="{{ $val }}" @selected(old('status',$asset->status)==$val)>{{ $label }}</option>
            @endforeach
          </select>
          @error('status') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Tanggal Commissioning</label>
          <input type="date" name="commissioned_at"
                 value="{{ old('commissioned_at', optional($asset->commissioned_at)->format('Y-m-d')) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('commissioned_at') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
      </div>
    </div>

    {{-- Section: Finansial --}}
    <div>
      <div class="text-xs font-semibold text-slate-500 mb-2">Finansial</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Acquisition Cost</label>
          <input type="number" step="0.01" name="acq_cost" value="{{ old('acq_cost',$asset->acq_cost) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('acq_cost') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Acquisition Date</label>
          <input type="date" name="acq_date"
                 value="{{ old('acq_date', optional($asset->acq_date)->format('Y-m-d')) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
          @error('acq_date') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
      </div>
    </div>

    {{-- Section: Lokasi --}}
    <div>
      <div class="text-xs font-semibold text-slate-500 mb-2">Lokasi</div>
      <input type="text" name="location" value="{{ old('location',$asset->location) }}"
             class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
      @error('location') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Section: Extra --}}
    <div>
      <div class="text-xs font-semibold text-slate-500 mb-2">Extra</div>
      <label class="block text-sm font-medium text-slate-700">Extra (JSON / text)</label>
      <textarea name="extra" rows="4"
                class="mt-1 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:ring-emerald-600 focus:border-emerald-600">{{ old('extra', is_array($asset->extra) ? json_encode($asset->extra, JSON_UNESCAPED_UNICODE) : ($asset->extra ?? '')) }}</textarea>
      @error('extra') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Footer Actions --}}
    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
      <a href="{{ route('admin.assets.index') }}"
         class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
        Batal
      </a>
      <button type="submit"
              class="px-4 py-2 rounded-xl font-semibold text-white
                     bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700
                     hover:from-emerald-700 hover:to-sky-800 shadow">
        {{ $asset->exists ? 'Simpan Perubahan' : 'Simpan Asset' }}
      </button>
    </div>
  </form>
</div>
@endsection
