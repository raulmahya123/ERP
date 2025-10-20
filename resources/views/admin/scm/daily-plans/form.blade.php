@extends('layouts.app')
@section('title', $item->exists ? 'Edit Daily Plan':'Tambah Daily Plan')
@section('content')
<h1 class="text-xl font-semibold mb-4">{{ $item->exists ? 'Edit' : 'Tambah' }} Daily Plan</h1>

@if ($errors->any())
  <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-3">
    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ $item->exists ? route('scm.daily-plans.update',$item->id) : route('scm.daily-plans.store') }}" class="space-y-3">
  @csrf @if($item->exists) @method('PUT') @endif

  <div class="grid md:grid-cols-3 gap-3">
    <div>
      <label class="block text-sm mb-1">Tanggal</label>
      <input type="date" name="plan_date" value="{{ old('plan_date', optional($item->plan_date)->format('Y-m-d')) }}" class="w-full border rounded px-2 py-1" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Shift ID</label>
      <input name="shift_id" value="{{ old('shift_id',$item->shift_id) }}" class="w-full border rounded px-2 py-1" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Catatan</label>
      <input name="remarks" value="{{ old('remarks',$item->remarks) }}" class="w-full border rounded px-2 py-1">
    </div>
  </div>

  <div class="mt-4">
    <h3 class="font-semibold">Items (Pit & Target)</h3>
    <div id="items" class="space-y-2">
      @php $rows = old('items', $items?->toArray() ?? [['pit_id'=>'','target_ton'=>'','target_ritase'=>'','notes'=>'']]); @endphp
      @foreach($rows as $i => $row)
      <div class="grid md:grid-cols-4 gap-2">
        <input name="items[{{ $i }}][pit_id]" placeholder="PIT ID" value="{{ $row['pit_id'] ?? '' }}" class="border rounded px-2 py-1" required>
        <input name="items[{{ $i }}][target_ton]" placeholder="Target Ton" value="{{ $row['target_ton'] ?? '' }}" class="border rounded px-2 py-1" required>
        <input name="items[{{ $i }}][target_ritase]" placeholder="Target Ritase" value="{{ $row['target_ritase'] ?? '' }}" class="border rounded px-2 py-1" required>
        <input name="items[{{ $i }}][notes]" placeholder="Catatan" value="{{ $row['notes'] ?? '' }}" class="border rounded px-2 py-1">
      </div>
      @endforeach
    </div>
    {{-- (Opsional) tombol JS untuk add row --}}
  </div>

  <div class="pt-2">
    <button class="px-4 py-1.5 bg-indigo-600 text-white rounded">{{ $item->exists ? 'Update':'Simpan' }}</button>
    <a href="{{ route('scm.daily-plans.index') }}" class="ml-2 underline">Batal</a>
  </div>
</form>
@endsection
