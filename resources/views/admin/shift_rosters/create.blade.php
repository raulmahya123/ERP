{{-- resources/views/admin/shift_rosters/create.blade.php --}}
@extends('layouts.app')
@section('title','Tambah Shift Roster')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

  {{-- ALERTS (global) --}}
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3 text-sm">
      <div class="font-semibold mb-1">Periksa kembali isian kamu:</div>
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Tambah Shift Roster</h1>
        <p class="text-white/85 text-sm">Tetapkan shift untuk user pada tanggal tertentu.</p>
      </div>
      <a href="{{ route('admin.shift-rosters.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- FORM --}}
  <form method="POST" action="{{ route('admin.shift-rosters.store') }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 space-y-5">
    @csrf

    @php
      $activeSiteId   = $activeSiteId ?? old('site_id', session('site_id'));
      $activeSite     = isset($sites) ? collect($sites)->firstWhere('id', $activeSiteId) : null;
      $activeSiteName = $activeSite ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name) : null;
    @endphp

    {{-- SITE --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Site</label>

      @if($activeSiteName)
        {{-- terkunci --}}
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
            </g>
          </svg>
          <span class="truncate font-medium">{{ $activeSiteName }}</span>
          <span class="ml-auto text-xs text-emerald-700">Terkunci</span>
        </div>
        <input type="hidden" name="site_id" id="site_id" value="{{ $activeSiteId }}">
      @else
        {{-- dropdown site --}}
        <select name="site_id" id="site_id"
                class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('site_id') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
                required>
          <option value="">— Pilih Site —</option>
          @foreach(($sites ?? []) as $s)
            @php $label = $s->code ? ($s->code.' — '.$s->name) : $s->name; @endphp
            <option value="{{ $s->id }}" {{ old('site_id')==$s->id ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        @error('site_id') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      @endif
    </div>

    {{-- USER (dropdown nama/kode; value = UUID) --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">User</label>

      <div class="grid gap-2">
        {{-- input pencarian ringan --}}
        <input id="user_search" type="text" placeholder="Cari nama / employee code…"
               class="w-full border rounded-2xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 border-emerald-200">

        {{-- select user --}}
        <select name="user_id" id="user_id" required
                class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('user_id') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror">
          <option value="">— Pilih User —</option>
          @foreach(($users ?? []) as $u)
            @php $ulabel = ($u->employee_code ? $u->employee_code.' — ' : '').$u->name; @endphp
            <option value="{{ $u->id }}" {{ old('user_id')==$u->id ? 'selected' : '' }}>
              {{ $ulabel }}
            </option>
          @endforeach
        </select>
      </div>

      @error('user_id') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      <p class="text-[11px] text-slate-500 mt-1">Ketik untuk mencari, daftar user akan diperbarui otomatis.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      {{-- TANGGAL --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Tanggal Roster</label>
        <input type="date" name="roster_date" required
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('roster_date') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('roster_date', request('date')) }}">
        @error('roster_date') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- SHIFT (dropdown; opsional) --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Shift</label>
        <select name="shift_id" id="shift_id"
                class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('shift_id') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror">
          <option value="">— Pilih Shift —</option>
          @foreach(($shifts ?? []) as $sh)
            @php $slabel = $sh->code ? ($sh->code.' — '.$sh->name) : $sh->name; @endphp
            <option value="{{ $sh->id }}" {{ old('shift_id')==$sh->id ? 'selected' : '' }}>
              {{ $slabel }}
            </option>
          @endforeach
        </select>
        @error('shift_id') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      {{-- CREW --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Crew Code</label>
        <input name="crew_code" maxlength="20"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('crew_code') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('crew_code') }}" placeholder="A1 / B2 / Team-01">
        @error('crew_code') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- REMARKS --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Remarks</label>
        <input name="remarks" maxlength="255"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('remarks') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('remarks') }}" placeholder="Catatan (opsional)">
        @error('remarks') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- ACTIONS --}}
    <div class="flex gap-2 justify-end">
      <a href="{{ route('admin.shift-rosters.index') }}"
         class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Batal</a>
      <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600">
        Simpan
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const siteSel   = document.getElementById('site_id');
    const shiftSel  = document.getElementById('shift_id');
    const userSel   = document.getElementById('user_id');
    const userInput = document.getElementById('user_search');

    async function reloadShifts(siteId) {
      if (!shiftSel) return;
      shiftSel.innerHTML = '<option value="">— Pilih Shift —</option>';
      if (!siteId) return;
      try {
        const url = "{{ route('admin.shift-rosters.shifts-by-site') }}?site_id=" + encodeURIComponent(siteId);
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
        const data = await res.json();
        const opts = ['<option value="">— Pilih Shift —</option>'];
        data.forEach(s => {
          const label = (s.code ? (s.code + ' — ') : '') + (s.name ?? '');
          opts.push(`<option value="${s.id}">${label}</option>`);
        });
        shiftSel.innerHTML = opts.join('');
      } catch (e) {
        console.error(e);
        shiftSel.innerHTML = '<option value="">— Pilih Shift —</option>';
      }
    }

    async function searchUsers(term, siteId) {
      if (!userSel) return;
      const current = userSel.value;
      userSel.innerHTML = '<option value="">Mencari…</option>';
      try {
        const params = new URLSearchParams();
        if (term) params.set('q', term);
        if (siteId) params.set('site_id', siteId);
        // gunakan endpoint yang sudah ada di routes: admin.hr-entries.search.users
        const url = "{{ route('admin.hr-entries.search.users') }}?" + params.toString();
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
        const list = await res.json();
        const opts = ['<option value="">— Pilih User —</option>'];
        list.forEach(u => {
          const label = (u.employee_code ? (u.employee_code + ' — ') : '') + (u.name ?? '');
          opts.push(`<option value="${u.id}">${label}</option>`);
        });
        userSel.innerHTML = opts.join('');
        // pertahankan selection sebelumnya jika masih ada di list
        if (current) {
          const found = Array.from(userSel.options).some(o => o.value === current);
          if (found) userSel.value = current;
        }
      } catch (e) {
        console.error(e);
        userSel.innerHTML = '<option value="">— Pilih User —</option>';
      }
    }

    // trigger saat dropdown site berubah (jika site tidak terkunci)
    if (siteSel && siteSel.tagName.toLowerCase() === 'select') {
      siteSel.addEventListener('change', e => {
        const sid = e.target.value;
        reloadShifts(sid);
        searchUsers(userInput?.value || '', sid);
      });
    }

    // live search user (debounce)
    if (userInput) {
      let t = null;
      userInput.addEventListener('input', e => {
        clearTimeout(t);
        t = setTimeout(() => {
          const sid = (siteSel && siteSel.tagName.toLowerCase() === 'select') ? siteSel.value : '{{ $activeSiteId ?? '' }}';
          searchUsers(e.target.value, sid);
        }, 300);
      });
    }
  });
</script>
@endpush
