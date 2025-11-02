@extends('layouts.app')

@section('title', $item->exists ? 'Edit Dispatch' : 'Tambah Dispatch')

@section('content')
  <h1 class="text-xl font-semibold mb-4">
    {{ $item->exists ? 'Edit' : 'Tambah' }} Dispatch
  </h1>

  @if ($errors->any())
    <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-3">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ $item->exists ? route('scm.dispatches.update', $item->id) : route('scm.dispatches.store') }}"
        class="space-y-4 max-w-4xl">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    {{-- Row 1: tanggal / shift / pit --}}
    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm mb-1">Tanggal</label>
        <input type="date"
               name="work_date"
               value="{{ old('work_date', optional($item->work_date)->format('Y-m-d')) }}"
               class="w-full border rounded px-2 py-1"
               required>
      </div>

      <div>
        <label class="block text-sm mb-1">Shift</label>
        <select name="shift_id" class="w-full border rounded px-2 py-1" required>
          <option value="" disabled @selected(!old('shift_id') && !$item->shift_id)>Pilih Shift…</option>
          @foreach(($shifts ?? []) as $s)
            <option value="{{ $s->id }}" @selected(old('shift_id', $item->shift_id) === $s->id)>{{ $s->name ?? $s->id }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm mb-1">Pit</label>
        <select name="pit_id" class="w-full border rounded px-2 py-1" required>
          <option value="" disabled @selected(!old('pit_id') && !$item->pit_id)>Pilih Pit…</option>
          @foreach(($pits ?? []) as $p)
            <option value="{{ $p->id }}" @selected(old('pit_id', $item->pit_id) === $p->id)>
              {{ ($p->code ?? 'PIT').' — '.($p->name ?? $p->id) }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Row 2: asset / operator / route --}}
    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm mb-1">Unit/Asset</label>
        <div class="flex gap-2">
          <select name="asset_id" class="w-full border rounded px-2 py-1" required>
            <option value="" disabled @selected(!old('asset_id') && !$item->asset_id)>Pilih Unit/Asset…</option>
            @foreach(($assets ?? []) as $a)
              <option value="{{ $a->id }}" @selected(old('asset_id', $item->asset_id) === $a->id)>
                {{ ($a->code ?? 'ASSET').' — '.($a->name ?? $a->id) }}
              </option>
            @endforeach
          </select>

          @if(($assets ?? collect())->isEmpty())
            {{-- Ganti route di bawah jika berbeda --}}
            @if (Route::has('admin.assets.create'))
              <a href="{{ route('admin.assets.create') }}"
                 class="px-3 py-1.5 rounded border text-sm">+ Tambah</a>
            @endif
          @endif
        </div>

        @if(($assets ?? collect())->isEmpty())
          <p class="mt-1 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
            Belum ada asset untuk site aktif. Tambahkan data asset terlebih dahulu.
          </p>
        @endif
      </div>

      <div>
        <label class="block text-sm mb-1">Operator</label>
        <select name="operator_id" class="w-full border rounded px-2 py-1" required>
          <option value="" disabled @selected(!old('operator_id') && !$item->operator_id)>Pilih Operator…</option>
          @foreach(($operators ?? []) as $u)
            <option value="{{ $u->id }}" @selected(old('operator_id', $item->operator_id) === $u->id)>
              {{ $u->name ?? $u->email ?? $u->id }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm mb-1">Rute (opsional)</label>
        <input name="route_id"
               value="{{ old('route_id', $item->route_id) }}"
               class="w-full border rounded px-2 py-1"
               placeholder="Route ID (jika ada)">
      </div>
    </div>

    {{-- Row 3: waktu / status --}}
    @php
      $startVal = old('planned_start',
        $item->planned_start instanceof \Carbon\Carbon
          ? $item->planned_start->format('H:i')
          : \Illuminate\Support\Str::substr((string)$item->planned_start, 0, 5)
      );
      $endVal = old('planned_end',
        $item->planned_end instanceof \Carbon\Carbon
          ? $item->planned_end->format('H:i')
          : \Illuminate\Support\Str::substr((string)$item->planned_end, 0, 5)
      );
    @endphp

    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm mb-1">Mulai (HH:mm)</label>
        <input type="time" name="planned_start"
               value="{{ $startVal ?: '' }}"
               class="w-full border rounded px-2 py-1" placeholder="07:00">
      </div>

      <div>
        <label class="block text-sm mb-1">Selesai (HH:mm)</label>
        <input type="time" name="planned_end"
               value="{{ $endVal ?: '' }}"
               class="w-full border rounded px-2 py-1" placeholder="19:00">
      </div>

      <div>
        <label class="block text-sm mb-1">Status</label>
        @php $statuses = ['planned','in_progress','done','cancelled']; @endphp
        <select name="status" class="w-full border rounded px-2 py-1" required>
          @foreach($statuses as $st)
            <option value="{{ $st }}" @selected(old('status', $item->status ?: 'planned') === $st)>
              {{ \Illuminate\Support\Str::upper($st) }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Row 4: catatan --}}
    <div>
      <label class="block text-sm mb-1">Catatan</label>
      <textarea name="notes" rows="3" class="w-full border rounded px-2 py-1"
                placeholder="Catatan (opsional)">{{ old('notes', $item->notes) }}</textarea>
    </div>

    <div class="pt-2">
      <button class="px-4 py-1.5 bg-indigo-600 text-white rounded">
        {{ $item->exists ? 'Update' : 'Simpan' }}
      </button>
      <a href="{{ route('scm.dispatches.index') }}" class="ml-2 underline">Batal</a>
    </div>
  </form>
@endsection
