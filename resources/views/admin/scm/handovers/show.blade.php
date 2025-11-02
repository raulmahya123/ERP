@extends('layouts.app')
@section('title','Detail Handover')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-xl font-semibold">Detail Handover</h1>
      <p class="text-slate-500 text-sm">
        {{ optional($handover->handover_date)->format('Y-m-d') }} —
        {{ $handover->from_shift_name ?? '-' }} → {{ $handover->to_shift_name ?? '-' }}
      </p>
    </div>
    <div class="space-x-2">
      <a href="{{ route('scm.handovers.index') }}" class="px-3 py-1.5 border rounded">Kembali</a>
      <a href="{{ route('scm.handovers.edit', $handover->id) }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Edit</a>
      <form action="{{ route('scm.handovers.destroy', $handover->id) }}" method="POST" class="inline">
        @csrf @method('DELETE')
        <button class="px-3 py-1.5 bg-rose-600 text-white rounded"
                onclick="return confirm('Hapus handover ini?')">Hapus</button>
      </form>
    </div>
  </div>

  <div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white border rounded p-4">
      <h2 class="font-semibold mb-2">Ringkasan</h2>
      <dl class="grid grid-cols-3 gap-2 text-sm">
        <dt class="text-slate-500">Tanggal</dt>
        <dd class="col-span-2">{{ optional($handover->handover_date)->format('Y-m-d') }}</dd>

        <dt class="text-slate-500">From Shift</dt>
        <dd class="col-span-2">{{ $handover->from_shift_name ?? '-' }}</dd>

        <dt class="text-slate-500">To Shift</dt>
        <dd class="col-span-2">{{ $handover->to_shift_name ?? '-' }}</dd>

        <dt class="text-slate-500">Cuaca</dt>
        <dd class="col-span-2">{{ $handover->weather ?: '-' }}</dd>
      </dl>
    </div>

    <div class="bg-white border rounded p-4">
      <h2 class="font-semibold mb-2">Catatan</h2>
      <div class="space-y-2 text-sm">
        <div>
          <div class="text-slate-500">Isu</div>
          <div class="whitespace-pre-wrap">{{ $handover->issues ?: '-' }}</div>
        </div>
        <div>
          <div class="text-slate-500">Target</div>
          <div class="whitespace-pre-wrap">{{ $handover->targets ?: '-' }}</div>
        </div>
        <div>
          <div class="text-slate-500">Keterangan</div>
          <div class="whitespace-pre-wrap">{{ $handover->notes ?: '-' }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-white border rounded mt-4">
    <div class="p-4 border-b">
      <h2 class="font-semibold">Detail per Pit</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="p-2 text-left">Pit</th>
            <th class="p-2">Catatan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $it)
            <tr class="border-t">
              <td class="p-2">
                @if ($it->pit_code || $it->pit_name)
                  {{ ($it->pit_code ?? 'PIT') . ' — ' . ($it->pit_name ?? '') }}
                @else
                  -
                @endif
              </td>
              <td class="p-2 whitespace-pre-wrap">
                {{ $it->notes ?: '-' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="p-3 text-center text-slate-500">Belum ada detail.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
