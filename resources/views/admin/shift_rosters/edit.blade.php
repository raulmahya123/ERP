{{-- resources/views/admin/shift_rosters/edit.blade.php --}}
@extends('layouts.app')
@section('title','Ubah Shift Roster')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

  {{-- ALERTS --}}  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3 text-sm">
      <div class="font-semibold mb-1">Periksa kembali isian kamu:</div>
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Ubah Shift Roster</h1>
        <p class="text-white/85 text-sm">Perbarui shift, crew, dan catatan roster ini.</p>
      </div>
      <a href="{{ route('admin.shift-rosters.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- INFO TERKUNCI --}}
  @php
    // jika controller mengirim $sites, tampilkan nama; kalau tidak, tampilkan UUID
    $siteLabel = $roster->site?->code
      ? ($roster->site->code.' — '.$roster->site->name)
      : ($roster->site->name ?? $roster->site_id);

    $userLabel = $roster->user?->name
      ? ($roster->user->employee_code ? $roster->user->employee_code.' — '.$roster->user->name : $roster->user->name)
      : $roster->user_id;
  @endphp

  <div class="grid md:grid-cols-3 gap-3">
    <div>
      <div class="text-xs text-slate-600 mb-1">Site</div>
      <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
        <span class="truncate font-medium">{{ $siteLabel }}</span>
        <span class="ml-auto text-[11px] text-emerald-700">Terkunci</span>
      </div>
    </div>
    <div>
      <div class="text-xs text-slate-600 mb-1">User</div>
      <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
        <span class="truncate font-medium">{{ $userLabel }}</span>
        <span class="ml-auto text-[11px] text-emerald-700">Terkunci</span>
      </div>
    </div>
    <div>
      <div class="text-xs text-slate-600 mb-1">Tanggal Roster</div>
      <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
        <span class="font-medium">
          {{ \Illuminate\Support\Carbon::parse($roster->roster_date)->format('Y-m-d') }}
        </span>
        <span class="ml-auto text-[11px] text-emerald-700">Terkunci</span>
      </div>
    </div>
  </div>

  {{-- FORM --}}
  <form method="POST" action="{{ route('admin.shift-rosters.update', $roster) }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 space-y-5">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-2 gap-3">
      {{-- SHIFT DROPDOWN --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Shift</label>
        <select name="shift_id"
                class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('shift_id') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror">
          <option value="">— Pilih Shift —</option>
          @foreach(($shifts ?? []) as $s)
            @php $label = $s->code ? ($s->code.' — '.$s->name) : $s->name; @endphp
            <option value="{{ $s->id }}" {{ old('shift_id',$roster->shift_id)===$s->id ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
        @error('shift_id') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
        @if(empty($shifts) || count($shifts)===0)
          <p class="text-[12px] text-slate-500 mt-1">Tidak ada shift untuk site ini. Buat shift dulu di menu Shift.</p>
        @endif
      </div>

      {{-- CREW CODE --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Crew Code</label>
        <input name="crew_code" maxlength="20"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('crew_code') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('crew_code',$roster->crew_code) }}" placeholder="A1 / B2 / Team-01">
        @error('crew_code') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- REMARKS --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Remarks</label>
      <input name="remarks" maxlength="255"
             class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('remarks') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
             value="{{ old('remarks',$roster->remarks) }}" placeholder="Catatan (opsional)">
      @error('remarks') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex gap-2 justify-end">
      <a href="{{ route('admin.shift-rosters.index') }}"
         class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Kembali</a>
      <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600">
        Simpan
      </button>
    </div>
  </form>
</div>
@endsection
