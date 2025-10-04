{{-- resources/views/admin/commodities/form.blade.php --}}
@extends('layouts.app')
@section('title', $mode === 'create' ? 'Tambah Komoditas' : 'Edit Komoditas')

@section('content')
    <div class="max-w-xl space-y-6">

        <div>
            <h1 class="text-xl font-semibold text-slate-800">
                {{ $mode === 'create' ? 'Tambah Komoditas' : 'Edit Komoditas' }}
            </h1>
            <p class="text-slate-500 text-sm">Isi kode (pilihan tetap) dan nama komoditas.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ $mode === 'create' ? route('admin.commodities.store') : route('admin.commodities.update', $commodity) }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            {{-- KODE (enum) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700">Kode *</label>
                <select name="code" class="mt-1 border rounded w-full px-3 py-2" required>
                    <option value="">— Pilih Kode —</option>
                    @foreach (\App\Models\Commodity::codeOptions() as $val => $label)
                        <option value="{{ $val }}" @selected(old('code', $commodity->code) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">
                    Pilihan: <b>Batubara</b>, <b>Nikel</b>, <b>Emas</b>.
                </p>
            </div>

            {{-- NAMA --}}
            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700">Nama *</label>
                <input type="text"
                       name="name"
                       class="mt-1 border rounded w-full px-3 py-2"
                       placeholder="cth: Batubara / Nikel / Emas"
                       value="{{ old('name', $commodity->name) }}"
                       required>
            </div>

            <div class="flex items-center gap-2 mt-6">
                <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                    {{ $mode === 'create' ? 'Simpan' : 'Update' }}
                </button>
                <a href="{{ route('admin.commodities.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
