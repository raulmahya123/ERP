@extends('layouts.app')

@section('title', $asset->exists ? 'Edit Asset' : 'Tambah Asset')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- Header --}}
  <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] text-white flex items-start justify-between">
    <div>
      <h1 class="text-lg font-bold">
        {{ $asset->exists ? '✏️ Edit Asset' : '➕ Tambah Asset' }}
      </h1>
      <p class="text-white/70 text-sm">Isi detail asset untuk site aktif.</p>

      @if(!empty($currentSite))
        <div class="mt-2 text-[11px]">
          <span class="px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">
            Site: <strong class="ml-1">{{ $currentSite->code }}</strong>
          </span>
          <a href="{{ route('sites.select') }}" class="underline decoration-white/50 hover:decoration-white ml-2">ganti</a>
        </div>
      @endif
    </div>

    <a href="{{ route('admin.assets.index') }}"
       class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm ring-1 ring-white/30 hover:bg-white/20">
      ← Kembali
    </a>
  </div>

  {{-- Flash & Errors --}}
  @if(session('status') || session('success'))
    <div class="px-6 py-3 bg-emerald-50 text-emerald-700 text-sm border-b border-emerald-200">
      {{ session('status') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-6 py-3 bg-red-50 text-red-700 text-sm border-b border-red-200">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ $asset->exists ? route('admin.assets.update',$asset) : route('admin.assets.store') }}"
        class="p-6 space-y-5">
    @csrf
    @if($asset->exists) @method('PUT') @endif

    {{-- Code & Name --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Kode</label>
        <input type="text" name="code" value="{{ old('code',$asset->code) }}"
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('code') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Nama *</label>
        <input type="text" name="name" value="{{ old('name',$asset->name) }}" required
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Category & Cost Center --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <div class="flex items-center justify-between">
          <label class="block text-sm font-medium text-slate-700">Kategori Asset</label>
          @if($categories->isEmpty() && Route::has('admin.master.index'))
            <a href="{{ route('admin.master.index',['entity'=>'asset_categories']) }}"
               class="text-[11px] text-teal-700 underline">isi master</a>
          @endif
        </div>
        <select name="asset_category_id"
                class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— pilih —</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('asset_category_id',$asset->asset_category_id)==$cat->id)>
              {{ $cat->name }}{{ $cat->code ? " ({$cat->code})" : '' }}
            </option>
          @endforeach
        </select>
        @error('asset_category_id') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label class="block text-sm font-medium text-slate-700">Cost Center</label>
          @if($costCenters->isEmpty() && Route::has('admin.master.index'))
            <a href="{{ route('admin.master.index',['entity'=>'cost_centers']) }}"
               class="text-[11px] text-teal-700 underline">isi master</a>
          @endif
        </div>
        <select name="cost_center_id"
                class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— pilih —</option>
          @foreach($costCenters as $cc)
            <option value="{{ $cc->id }}" @selected(old('cost_center_id',$asset->cost_center_id)==$cc->id)>
              {{ $cc->name }}{{ $cc->code ? " ({$cc->code})" : '' }}
            </option>
          @endforeach
        </select>
        @error('cost_center_id') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Specs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Brand</label>
        <input type="text" name="brand" value="{{ old('brand',$asset->brand) }}"
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('brand') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Model</label>
        <input type="text" name="model" value="{{ old('model',$asset->model) }}"
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('model') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Serial No</label>
        <input type="text" name="serial_no" value="{{ old('serial_no',$asset->serial_no) }}"
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('serial_no') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Plate No / Unit No</label>
        <input type="text" name="plate_no" value="{{ old('plate_no',$asset->plate_no) }}"
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('plate_no') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Status & Commissioning --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status"
                class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
          @foreach(['active'=>'Active','inactive'=>'Inactive','repair'=>'Repair','sold'=>'Sold','disposed'=>'Disposed'] as $val=>$label)
            <option value="{{ $val }}" @selected(old('status',$asset->status)==$val)>{{ $label }}</option>
          @endforeach
        </select>
        @error('status') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Tanggal Commissioning</label>
        <input type="date" name="commissioned_at"
               value="{{ old('commissioned_at', optional($asset->commissioned_at)->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('commissioned_at') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Financial --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Acquisition Cost</label>
        <input type="number" step="0.01" name="acq_cost" value="{{ old('acq_cost',$asset->acq_cost) }}"
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('acq_cost') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Acquisition Date</label>
        <input type="date" name="acq_date"
               value="{{ old('acq_date', optional($asset->acq_date)->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('acq_date') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Lokasi --}}
    <div>
      <label class="block text-sm font-medium text-slate-700">Lokasi</label>
      <input type="text" name="location" value="{{ old('location',$asset->location) }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
      @error('location') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Extra JSON --}}
    <div>
      <label class="block text-sm font-medium text-slate-700">Extra (JSON / text)</label>
      <textarea name="extra" rows="3"
                class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">{{ old('extra', is_array($asset->extra) ? json_encode($asset->extra) : ($asset->extra ?? '')) }}</textarea>
      @error('extra') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t">
      <a href="{{ route('admin.assets.index') }}"
         class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">Batal</a>
      <button type="submit"
              class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
        {{ $asset->exists ? 'Simpan Perubahan' : 'Simpan Asset' }}
      </button>
    </div>
  </form>
</div>
@endsection
