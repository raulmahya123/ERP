{{-- resources/views/admin/hr_entries/_form.blade.php --}}
@php
  // Props yang diharapkan:
  // $types, $activeSiteId, optional: $users / $userOptions, optional: $entry
  $isEdit   = isset($entry);
  $val      = fn($k,$d=null)=> old($k, $isEdit ? data_get($entry,$k) : $d);
  $meta     = (array) old('meta', $isEdit ? (array) ($entry->meta ?? []) : []);
  $metaVal  = fn($key,$def=null)=> data_get($meta,$key,$def);
  $curType  = $val('type','leave');
@endphp

@push('styles')
<style>
  [x-cloak]{display:none}
  .card{ @apply bg-white rounded-2xl border border-slate-200 shadow-sm }
  .card-sec{ @apply p-4 md:p-6 }
  .label{ @apply block text-xs font-medium text-slate-600 mb-1 }
  .hint{ @apply text-[11px] leading-4 text-slate-500 mt-1 }
  .input{ @apply w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 }
  .select{ @apply w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 }
  .field{ @apply space-y-1 }
  .chip{ @apply inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 }
  .danger{ @apply text-[12px] text-rose-600 mt-1 }
  .section-title{ @apply text-sm font-semibold text-slate-800 }
  .divider{ @apply h-px bg-slate-200 my-3 }
  .toolbar{ @apply sticky bottom-0 z-10 bg-white/80 backdrop-blur border-t border-slate-200 }
  .btn{ @apply inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 }
  .btn-primary{ @apply border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 }
  .btn-ghost{ @apply border-transparent bg-transparent hover:bg-slate-100 text-slate-600 }
  .icon{ width:16px; height:16px }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('hrForm', () => ({
      type: @json($curType),
      get needsShift(){ return this.type === 'shift_change' },
      // UX: auto-clear shift fields when out of scope
      clearIfNotShift(el){
        if (!this.needsShift && el?.value) el.value = ''
      }
    }))
  })
</script>
@endpush

<div x-data="hrForm" class="space-y-6">

  {{-- ===== HEADER CARD ===== --}}
  <div class="card">
    <div class="card-sec flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <div class="flex items-center gap-2 text-xs text-slate-500">
          <span>HR</span>
          <svg class="icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 0 1 0-1.414L10.586 10 7.293 6.707a1 1 0 1 1 1.414-1.414l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 0 1-1.414 0Z"/></svg>
          <span>Daily Entry</span>
          @if($isEdit)
            <svg class="icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 0 1 0-1.414L10.586 10 7.293 6.707a1 1 0 1 1 1.414-1.414l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 0 1-1.414 0Z"/></svg>
            <span>Edit</span>
          @else
            <svg class="icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 0 1 0-1.414L10.586 10 7.293 6.707a1 1 0 1 1 1.414-1.414l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 0 1-1.414 0Z"/></svg>
            <span>Create</span>
          @endif
        </div>
        <h2 class="mt-1 text-xl font-bold text-slate-900">Form HR Daily Entry</h2>
        <p class="text-sm text-slate-500">Izin • Cuti • Sakit • Mutasi Shift — UI bersih, simple, dan elegan.</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="chip">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 1 7 7v2h1a2 2 0 1 1 0 4h-1v2a7 7 0 1 1-14 0v-2H4a2 2 0 1 1 0-4h1V9a7 7 0 0 1 7-7Z"/></svg>
          Site Aktif: <span class="font-bold">{{ $activeSiteId ?? '—' }}</span>
        </span>
        <a href="{{ route('admin.sites.index') ?? '#' }}" class="btn-ghost">Ganti</a>
      </div>
    </div>
  </div>

  {{-- ===== CORE FIELDS ===== --}}
  <div class="card">
    <div class="card-sec space-y-4">
      <div class="flex items-center justify-between">
        <span class="section-title">Data Utama</span>
      </div>
      <div class="divider"></div>

      <div class="grid md:grid-cols-3 gap-4">
        {{-- USER --}}
        <div class="field">
          <label class="label">User</label>
          @if(isset($users) && count($users))
            <select name="user_id" class="select" {{ $isEdit ? 'disabled' : '' }} required>
              <option value="">— Pilih —</option>
              @foreach($users as $u)
                <option value="{{ $u->id }}" @selected($val('user_id')===$u->id)>{{ $u->name }}</option>
              @endforeach
            </select>
            @if($isEdit)<input type="hidden" name="user_id" value="{{ $val('user_id') }}">@endif
          @elseif(isset($userOptions) && is_array($userOptions))
            <select name="user_id" class="select" {{ $isEdit ? 'disabled' : '' }} required>
              <option value="">— Pilih —</option>
              @foreach($userOptions as $id=>$label)
                <option value="{{ $id }}" @selected($val('user_id')===$id)>{{ $label }}</option>
              @endforeach
            </select>
            @if($isEdit)<input type="hidden" name="user_id" value="{{ $val('user_id') }}">@endif
          @else
            <input type="text" name="user_id" value="{{ $val('user_id') }}" class="input" {{ $isEdit ? 'readonly' : '' }} required>
          @endif
          @error('user_id')<div class="danger">{{ $message }}</div>@enderror
          <p class="hint">User bebas dipilih; site mengikuti <b>Site Aktif</b> saat simpan.</p>
        </div>

        {{-- TANGGAL --}}
        <div class="field">
          <label class="label">Tanggal</label>
          <input type="date" name="date"
                 value="{{ \Illuminate\Support\Str::of($val('date'))->substr(0,10) }}"
                 class="input" {{ $isEdit ? 'readonly' : '' }} required>
          @error('date')<div class="danger">{{ $message }}</div>@enderror
        </div>

        {{-- TYPE --}}
        <div class="field">
          <label class="label">Type</label>
          <select name="type" x-model="type" class="select" {{ $isEdit ? 'disabled' : '' }} required>
            @foreach($types as $k=>$v)
              <option value="{{ $k }}" @selected($curType===$k)>{{ $v }}</option>
            @endforeach
          </select>
          @if($isEdit)<input type="hidden" name="type" value="{{ $curType }}">@endif
          @error('type')<div class="danger">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="grid md:grid-cols-3 gap-4">
        {{-- KODE --}}
        <div class="field">
          <label class="label">Kode (opsional)</label>
          <input type="text" name="code" value="{{ $val('code') }}" class="input">
          @error('code')<div class="danger">{{ $message }}</div>@enderror
        </div>

        {{-- SHIFT FROM --}}
        <div class="field" x-show="needsShift" x-cloak>
          <label class="label">From Shift ID</label>
          <input type="text" name="from_shift_id" value="{{ $val('from_shift_id') }}" class="input" :required="needsShift" x-on:change="clearIfNotShift($event.target)">
          @error('from_shift_id')<div class="danger">{{ $message }}</div>@enderror
        </div>

        {{-- SHIFT TO --}}
        <div class="field" x-show="needsShift" x-cloak>
          <label class="label">To Shift ID</label>
          <input type="text" name="to_shift_id" value="{{ $val('to_shift_id') }}" class="input" :required="needsShift" x-on:change="clearIfNotShift($event.target)">
          @error('to_shift_id')<div class="danger">{{ $message }}</div>@enderror
          <p class="hint">Harus berbeda dengan <b>From Shift</b>.</p>
        </div>
      </div>

      {{-- REASON --}}
      <div class="field">
        <label class="label">Reason</label>
        <textarea name="reason" rows="3" class="input" placeholder="Tuliskan alasan singkat…">{{ $val('reason') }}</textarea>
        @error('reason')<div class="danger">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>

  {{-- ===== META DINAMIS ===== --}}
  <div class="card">
    <div class="card-sec space-y-4">
      <div class="flex items-center justify-between">
        <span class="section-title">Detail Tambahan</span>
        <span class="text-xs text-slate-500">Menyesuaikan tipe yang dipilih</span>
      </div>
      <div class="divider"></div>

      {{-- LEAVE --}}
      <template x-if="type==='leave'">
        <div class="grid md:grid-cols-4 gap-4">
          <div class="field">
            <label class="label">Leave Type</label>
            <select name="meta[leave_type]" class="select" required>
              @foreach(['annual'=>'Annual','unpaid'=>'Unpaid','marriage'=>'Marriage','maternity'=>'Maternity','paternity'=>'Paternity','other'=>'Other'] as $k=>$v)
                <option value="{{ $k }}" @selected($metaVal('leave_type')===$k)>{{ $v }}</option>
              @endforeach
            </select>
            @error('meta.leave_type')<div class="danger">{{ $message }}</div>@enderror
          </div>
          <div class="field">
            <label class="label">Durasi (hari)</label>
            <input type="number" min="0" step="0.5" name="meta[duration_days]" value="{{ $metaVal('duration_days') }}" class="input">
            @error('meta.duration_days')<div class="danger">{{ $message }}</div>@enderror
          </div>
          <div class="field flex items-end">
            <label class="inline-flex items-center gap-2 text-sm">
              <input type="checkbox" name="meta[half_day]" value="1" @checked($metaVal('half_day'))>
              <span>Half-day</span>
            </label>
          </div>
          <div class="field">
            <label class="label">Attachment ID</label>
            <input type="text" name="meta[attachment_id]" value="{{ $metaVal('attachment_id') }}" class="input">
          </div>
          <div class="md:col-span-4 field">
            <label class="label">Catatan</label>
            <input type="text" name="meta[notes]" value="{{ $metaVal('notes') }}" class="input">
          </div>
        </div>
      </template>

      {{-- PERMIT --}}
      <template x-if="type==='permit'">
        <div class="grid md:grid-cols-4 gap-4">
          <div class="field">
            <label class="label">Kategori Izin</label>
            <select name="meta[permit_category]" class="select" required>
              @foreach(['personal'=>'Personal','official'=>'Official','urgent'=>'Urgent','other'=>'Other'] as $k=>$v)
                <option value="{{ $k }}" @selected($metaVal('permit_category')===$k)>{{ $v }}</option>
              @endforeach
            </select>
            @error('meta.permit_category')<div class="danger">{{ $message }}</div>@enderror
          </div>
          <div class="field">
            <label class="label">Jam (durasi)</label>
            <input type="number" min="0" step="0.5" name="meta[hours]" value="{{ $metaVal('hours') }}" class="input">
          </div>
          <div class="field">
            <label class="label">Mulai (HH:MM)</label>
            <input type="time" name="meta[start_time]" value="{{ $metaVal('start_time') }}" class="input">
          </div>
          <div class="field">
            <label class="label">Selesai (HH:MM)</label>
            <input type="time" name="meta[end_time]" value="{{ $metaVal('end_time') }}" class="input">
          </div>
          <div class="field">
            <label class="label">Attachment ID</label>
            <input type="text" name="meta[attachment_id]" value="{{ $metaVal('attachment_id') }}" class="input">
          </div>
          <div class="md:col-span-3 field">
            <label class="label">Catatan</label>
            <input type="text" name="meta[notes]" value="{{ $metaVal('notes') }}" class="input">
          </div>
        </div>
      </template>

      {{-- SICK --}}
      <template x-if="type==='sick'">
        <div class="grid md:grid-cols-4 gap-4">
          <div class="field md:col-span-2">
            <div class="grid grid-cols-3 gap-3">
              <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="meta[doctor_note]" value="1" @checked($metaVal('doctor_note'))>
                <span>Surat Dokter</span>
              </label>
              <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="meta[inpatient]" value="1" @checked($metaVal('inpatient'))>
                <span>Rawat Inap</span>
              </label>
              <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="meta[bpjs_claim]" value="1" @checked($metaVal('bpjs_claim'))>
                <span>BPJS Claim</span>
              </label>
            </div>
          </div>
          <div class="field">
            <label class="label">Attachment ID</label>
            <input type="text" name="meta[attachment_id]" value="{{ $metaVal('attachment_id') }}" class="input">
          </div>
          <div class="md:col-span-4 field">
            <label class="label">Diagnosis</label>
            <input type="text" name="meta[diagnosis]" value="{{ $metaVal('diagnosis') }}" class="input">
          </div>
          <div class="md:col-span-4 field">
            <label class="label">Catatan</label>
            <input type="text" name="meta[notes]" value="{{ $metaVal('notes') }}" class="input">
          </div>
        </div>
      </template>

      {{-- SHIFT CHANGE --}}
      <template x-if="type==='shift_change'">
        <div class="grid md:grid-cols-4 gap-4">
          <div class="field">
            <label class="label">Efektif Dari</label>
            <input type="date" name="meta[effective_from]" value="{{ $metaVal('effective_from') }}" class="input">
          </div>
          <div class="field">
            <label class="label">Requested By (User ID)</label>
            <input type="text" name="meta[requested_by]" value="{{ $metaVal('requested_by') }}" class="input">
          </div>
          <div class="field">
            <label class="label">Approved By (User ID)</label>
            <input type="text" name="meta[approved_by]" value="{{ $metaVal('approved_by') }}" class="input">
          </div>
          <div class="md:col-span-4 field">
            <label class="label">Catatan</label>
            <input type="text" name="meta[notes]" value="{{ $metaVal('notes') }}" class="input">
          </div>
        </div>
      </template>
    </div>

    {{-- ACTION BAR --}}
    <div class="toolbar card-sec flex items-center justify-between">
      <div class="text-[12px] text-slate-500">
        Pastikan data sudah benar. Perubahan tipe akan menampilkan field yang relevan.
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ url()->previous() }}" class="btn">Batal</a>
        <button class="btn-primary">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 12a1 1 0 0 1 1-1h5V6a1 1 0 1 1 2 0v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H6a1 1 0 0 1-1-1Z"/></svg>
          {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Entry' }}
        </button>
      </div>
    </div>
  </div>
</div>
