{{-- resources/views/admin/payroal_history/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Generate Draft Payslip')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
  <h1 class="text-xl font-bold text-slate-800 mb-4">Generate Draft Payslip</h1>

  <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl p-4">
    <form method="POST" action="{{ route('admin.payroal_history.store') }}">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs text-slate-500 mb-1">Periode</label>
          <input type="month" name="period" value="{{ old('period') }}" required
                 class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500">
          @error('period') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-xs text-slate-500 mb-1">Site (opsional)</label>
          <select name="site_id" class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500">
            <option value="">— Semua Site —</option>
            @isset($sites)
              @foreach($sites as $s)
                <option value="{{ $s->id }}" @selected(old('site_id')==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
              @endforeach
            @endisset
          </select>
          @error('site_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs text-slate-500 mb-1">Catatan (opsional)</label>
          <textarea name="note" rows="4"
                    class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500"
                    placeholder="Catatan internal / keterangan tambahan...">{{ old('note') }}</textarea>
          @error('note') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="mt-5 flex items-center gap-2">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">
          Generate
        </button>
        <a href="{{ route('admin.payroal_history.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-slate-200 text-slate-700 hover:bg-slate-300">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
