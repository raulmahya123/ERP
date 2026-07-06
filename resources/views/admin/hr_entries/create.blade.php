{{-- resources/views/admin/hr_entries/create.blade.php --}}
@extends('layouts.app')
@section('title','Create HR Entry')

@section('content')
{{-- ===== SVG SPRITE ===== --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-save" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke-width="2" stroke-linejoin="round"/>
    <path d="M17 21v-8H7v8M7 3v5h8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2"/><path d="M9 11V8a3 3 0 1 1 6 0v3"/>
    </g>
  </symbol>
</svg>

@php
  // Controller sudah kirim: $types, $activeSiteId, $activeSiteLabel, $users, $shifts, $resolvedType, $metaFields
  $types   = $types ?? [];
  $users   = $users ?? collect();
  $shifts  = $shifts ?? collect();
@endphp

<div class="max-w-5xl mx-auto space-y-6">

  {{-- HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Create HR Daily Entry</h1>
        <p class="text-white/85 text-sm">Buat pengajuan leave / permit / sick / shift change.</p>
      </div>
      <a href="{{ route('admin.hr-entries.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg>
        Back
      </a>
    </div>
  </div>

  {{-- ALERTS --}}  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM CARD --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6">
    <form method="POST" action="{{ route('admin.hr-entries.store') }}" x-data="{ saveContinue:false }" novalidate autocomplete="off">
      @csrf

      {{-- SITE (LOCKED, tampil nama/kode, kirim UUID tersembunyi) --}}
      <div class="mb-3">
        <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
          <span class="truncate">{{ $activeSiteLabel ?? '—' }}</span>
          <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
            <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Locked
          </span>
        </div>
        <input type="hidden" name="site_id" value="{{ old('site_id', $activeSiteId) }}">
        @error('site_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- USER (tampil nama; kirim UUID via hidden) --}}
      <div class="mb-3">
        <label class="block text-xs text-slate-600 mb-1">User</label>
        <div class="relative">
          <input id="user-name-input"
                 name="user_name"
                 list="users-list"
                 value="{{ old('user_name') }}"
                 placeholder="Ketik nama lalu pilih"
                 class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          <input type="hidden" id="user-id-hidden" name="user_id" value="{{ old('user_id') }}">
        </div>
        <datalist id="users-list">
          @foreach($users as $u)
            <option data-id="{{ $u->id }}" value="{{ $u->name }}{{ $u->employee_code ? ' — '.$u->employee_code : '' }}"></option>
          @endforeach
        </datalist>
        <p class="mt-1 text-[11px] text-slate-500">Pilih dari daftar—UUID tidak ditampilkan, tapi dikirim tersembunyi.</p>
        @error('user_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- DATE + TYPE --}}
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-slate-600 mb-1">Date</label>
          <input type="date" name="date" value="{{ old('date') }}"
                 class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
          @error('date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1">Type</label>
          <select name="type" id="entry-type"
                  class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
            @foreach($types as $k => $label)
              <option value="{{ $k }}" @selected(old('type', $resolvedType ?? '') === $k)>{{ $label }}</option>
            @endforeach
          </select>
          @error('type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- CODE + REASON --}}
      <div class="grid md:grid-cols-2 gap-3 mt-3">
        <div>
          <label class="block text-xs text-slate-600 mb-1">Code (opsional)</label>
          <input type="text" name="code" value="{{ old('code') }}"
                 class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" maxlength="20">
          @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1">Reason (opsional)</label>
          <input type="text" name="reason" value="{{ old('reason') }}"
                 class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          @error('reason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- SHIFT CHANGE FIELDS (hanya untuk type=shift_change) --}}
      <div id="shift-fields" class="grid md:grid-cols-2 gap-3 mt-3" style="display:none;">
        <div>
          <label class="block text-xs text-slate-600 mb-1">From Shift</label>
          <select name="from_shift_id"
                  class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <option value="">—</option>
            @foreach($shifts as $s)
              <option value="{{ $s->id }}" @selected(old('from_shift_id')===$s->id)>{{ $s->code }} — {{ $s->name }}</option>
            @endforeach
          </select>
          @error('from_shift_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1">To Shift</label>
          <select name="to_shift_id"
                  class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <option value="">—</option>
            @foreach($shifts as $s)
              <option value="{{ $s->id }}" @selected(old('to_shift_id')===$s->id)>{{ $s->code }} — {{ $s->name }}</option>
            @endforeach
          </select>
          @error('to_shift_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- META FIELDS (auto dari $metaFields) --}}
      <div class="mt-4 space-y-3">
        <div class="text-sm font-semibold text-slate-700">Additional Fields</div>
        @php $metaOld = old('meta', []); @endphp

        @forelse(($metaFields ?? []) as $f)
          @php
            $key   = (string)($f['key'] ?? '');
            $label = (string)($f['label'] ?? Str::headline($key));
            $type  = (string)($f['type'] ?? 'text');
            $req   = !empty($f['required']);
            $opts  = is_array($f['options'] ?? null) ? $f['options'] : [];
            $val   = $metaOld[$key] ?? '';
            $name  = "meta[$key]";
          @endphp

          <div class="grid gap-1">
            <label class="text-xs text-slate-600">
              {{ $label }}{!! $req ? ' <span class="text-rose-600">*</span>' : '' !!}
            </label>

            @switch($type)
              @case('textarea')
                <textarea name="{{ $name }}" @required($req)
                          class="w-full min-h-[96px] border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old($name, $val) }}</textarea>
                @break

              @case('number')
                <input type="number" name="{{ $name }}" value="{{ old($name, $val) }}" @required($req)
                       class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                @break

              @case('date')
                <input type="date" name="{{ $name }}" value="{{ old($name, $val) }}" @required($req)
                       class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                @break

              @case('time')
                <input type="time" name="{{ $name }}" value="{{ old($name, $val) }}" @required($req)
                       class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                @break

              @case('select')
                <select name="{{ $name }}" @required($req)
                        class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                  <option value="">—</option>
                  @foreach($opts as $o)
                    @php
                      $oval = is_array($o) ? ($o['value'] ?? '') : $o;
                      $olbl = is_array($o) ? ($o['label'] ?? $oval) : Str::headline((string)$oval);
                    @endphp
                    <option value="{{ $oval }}" @selected(old($name, $val)==$oval)>{{ $olbl }}</option>
                  @endforeach
                </select>
                @break

              @case('radio')
                <div class="flex flex-wrap gap-3">
                  @foreach($opts as $o)
                    @php
                      $oval = is_array($o) ? ($o['value'] ?? '') : $o;
                      $olbl = is_array($o) ? ($o['label'] ?? $oval) : Str::headline((string)$oval);
                    @endphp
                    <label class="inline-flex items-center gap-2 text-sm">
                      <input type="radio" name="{{ $name }}" value="{{ $oval }}" @checked(old($name, $val)==$oval)>
                      <span>{{ $olbl }}</span>
                    </label>
                  @endforeach
                </div>
                @break

              @case('checkbox')
              @case('toggle')
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name,$val))>
                  <span class="text-sm text-slate-700">Ya/Tidak</span>
                </label>
                @break

              @case('file')
                {{-- Controller mengharapkan id/path, bukan upload fisik --}}
                <input type="text" name="{{ $name }}" value="{{ old($name, $val) }}" @required($req)
                       placeholder="Masukkan Attachment ID atau path file"
                       class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                @break

              @default
                <input type="text" name="{{ $name }}" value="{{ old($name, $val) }}" @required($req)
                       class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            @endswitch

            @error("meta.$key") <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
          </div>
        @empty
          <div class="text-sm text-slate-600">Tidak ada field tambahan.</div>
        @endforelse
      </div>

      <input type="hidden" name="save_continue" x-model="saveContinue">

      <div class="mt-6 flex flex-wrap items-center gap-2">
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 ring-1 ring-emerald-600">
          <svg class="h-4 w-4"><use href="#i-save"/></svg>
          Save
        </button>

        <button type="submit"
                @click.prevent="saveContinue = true; $el.form.submit()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 hover:bg-emerald-100">
          Save & Add Another
        </button>

        <a href="{{ route('admin.hr-entries.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const $type   = document.getElementById('entry-type');
  const $shift  = document.getElementById('shift-fields');
  const $uname  = document.getElementById('user-name-input');
  const $uid    = document.getElementById('user-id-hidden');
  const $dl     = document.getElementById('users-list');

  function toggleShiftFields(){
    const v = ($type?.value || '').trim();
    if($shift) $shift.style.display = (v === 'shift_change') ? '' : 'none';
  }
  toggleShiftFields();
  $type?.addEventListener('change', toggleShiftFields);

  function resolveUserIdFromLabel(label){
    if(!$dl) return null;
    const opts = Array.from($dl.options || []);
    const found = opts.find(o => (o.value || '').trim() === (label || '').trim());
    return found ? (found.dataset.id || null) : null;
  }
  function syncUserId(){
    const uid = resolveUserIdFromLabel($uname.value);
    if(uid){ $uid.value = uid; }
  }
  $uname?.addEventListener('change', syncUserId);
  $uname?.addEventListener('blur', syncUserId);
  $uname?.addEventListener('input', function(){ $uid.value=''; });

  document.querySelector('form')?.addEventListener('submit', function(e){
    if($uname && !$uid.value){
      const uid = resolveUserIdFromLabel($uname.value);
      if(uid){ $uid.value = uid; }
      else {
        e.preventDefault();
        alert('Silakan pilih user dari daftar supaya UUID-nya valid.');
        $uname.focus();
      }
    }
  });
})();
</script>
@endpush
