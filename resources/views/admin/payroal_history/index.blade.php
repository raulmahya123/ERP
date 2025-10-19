{{-- resources/views/admin/payroal_history/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Payslip Bulanan')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-7xl mx-auto">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Payslip Bulanan</h1>
          <p class="text-white/90 text-sm mt-1">Kelola draft, kunci slip gaji, dan kirim massal via email/link.</p>
        </div>

        <div class="flex items-center gap-2">
          @if(Route::has('admin.payroal_history.create'))
            <a href="{{ route('admin.payroal_history.create') }}"
               class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20">
              Generate Draft
            </a>
          @endif

          @if(Route::has('admin.payroal_history.sendBulk'))
            <form id="bulk-form" action="{{ route('admin.payroal_history.sendBulk') }}" method="POST">
              @csrf
              {{-- Mode B: by selected IDs (prioritas) --}}
              <input type="hidden" name="ids" id="bulk-ids">
              {{-- Mode A (fallback): ikutkan filter aktif --}}
              <input type="hidden" name="period"  value="{{ request('period') }}">
              <input type="hidden" name="site_id" value="{{ request('site_id') }}">
              <button type="submit" id="bulk-send-btn"
                      class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold bg-amber-600 text-white hover:bg-amber-700 ring-1 ring-amber-700/20 disabled:opacity-40 disabled:cursor-not-allowed"
                      disabled>
                Kirim Massal
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ALERTS --}}
  <div class="px-6 sm:px-10 pt-4 bg-white">
    @if ($errors->any())
      <div class="mb-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">
        <ul class="list-disc ms-4">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    @if (session('success'))
      <div class="mb-3 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">
        {{ session('success') }}
      </div>
    @endif
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 pb-5 bg-white border-b border-slate-100">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-[1.5fr_1fr_1fr_auto] gap-3">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Cari (nama / email / kode)</label>
        <div class="relative">
          <input type="text" name="q" value="{{ request('q','') }}"
                 class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-9 pr-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                 placeholder="Ketik kata kunci...">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
          </svg>
        </div>
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Periode (YYYY-MM)</label>
        <input type="month" name="period" value="{{ request('period') }}"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Site</label>
        <select name="site_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— Semua Site —</option>
          @isset($sites)
            @foreach($sites as $s)
              <option value="{{ $s->id }}" @selected(request('site_id')==$s->id)>
                {{ $s->code ?? '—' }} — {{ $s->name }}
              </option>
            @endforeach
          @endisset
        </select>
      </div>

      <div class="flex items-end gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-semibold shadow ring-1 ring-black/10 hover:bg-slate-900">
          Terapkan
        </button>
        @if(request()->hasAny(['q','period','site_id']))
          <a href="{{ route('admin.payroal_history.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
        @endif
      </div>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="p-6 bg-white">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50">
            <tr class="text-left text-slate-600">
              <th class="px-4 py-2 w-10">
                <input type="checkbox" id="check-all">
              </th>
              <th class="px-4 py-2">User</th>
              <th class="px-4 py-2">Email</th>
              <th class="px-4 py-2">Employee Code</th>
              <th class="px-4 py-2">Periode</th>
              <th class="px-4 py-2">Site</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse(($rows ?? []) as $row)
              @php
                $user      = optional(optional($row->payroal)->user);
                $site      = optional($row->site);
                $status    = (string)($row->status ?? 'draft');
                $isLocked  = in_array($status, ['locked','sent'], true);
                $hasEmail  = filled($user->email ?? null);
                $isSendable= $isLocked && $hasEmail;

                // Period display aman (Carbon cast -> format; fallback string)
                $periodText = method_exists($row->period ?? null, 'format')
                    ? $row->period->format('Y/m')
                    : \Illuminate\Support\Str::of((string)($row->period ?? ''))->replace('-','/')->toString();

                $sentAt = $row->sent_at ?? null;
              @endphp
              <tr class="hover:bg-emerald-50/40">
                <td class="px-4 py-2 align-middle">
                  <input type="checkbox" value="{{ $row->id }}" class="row-check" @disabled(!$isSendable)>
                </td>
                <td class="px-4 py-2 align-middle">{{ $user->name ?? '—' }}</td>
                <td class="px-4 py-2 align-middle">
                  {{ $user->email ?? '—' }}
                  @unless($hasEmail)
                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 ring-1 ring-rose-200 align-middle">no email</span>
                  @endunless
                </td>
                <td class="px-4 py-2 align-middle">{{ $row->employee_code ?? optional($row->payroal)->employee_code ?? '—' }}</td>
                <td class="px-4 py-2 align-middle font-mono">{{ $periodText ?: '—' }}</td>
                <td class="px-4 py-2 align-middle">
                  {{ $site->code ?? '—' }}{{ ($site->name ?? null) ? ' — '.$site->name : '' }}
                </td>
                <td class="px-4 py-2 align-middle">
                  <div class="flex flex-wrap items-center gap-1">
                    @switch($status)
                      @case('locked')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 ring-1 ring-slate-200">Locked</span>
                        @break
                      @case('sent')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">Sent</span>
                        @break
                      @default
                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 ring-1 ring-amber-200">Draft</span>
                    @endswitch
                    @if($sentAt)
                      <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                        {{ \Illuminate\Support\Str::of($sentAt)->before('.') }}
                      </span>
                    @endif
                  </div>
                </td>
                <td class="px-4 py-2 align-middle">
                  <div class="flex items-center gap-2">
                    {{-- Lock (only if not locked) --}}
                    @unless($isLocked)
                      <form action="{{ route('admin.payroal_history.lock', $row->id) }}" method="POST" class="inline"
                            onsubmit="return confirmLock(this, '{{ $user->name ?? 'pegawai' }}', '{{ $periodText }}')">
                        @csrf
                        <button class="px-2 py-1 rounded-md text-xs font-semibold bg-slate-200 hover:bg-slate-300">
                          Lock
                        </button>
                      </form>
                    @endunless

                    {{-- Kirim (only if sendable) --}}
                    @if($isSendable)
                      <form action="{{ route('admin.payroal_history.sendOne', $row->id) }}" method="POST" class="inline"
                            onsubmit="return confirmSendOne(this, '{{ $user->email ?? '' }}', '{{ $periodText }}')">
                        @csrf
                        <button class="px-2 py-1 rounded-md text-xs font-semibold bg-teal-600 text-white hover:bg-teal-700">
                          Kirim
                        </button>
                      </form>
                    @else
                      <button class="px-2 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-400 cursor-not-allowed" title="Kunci dulu & pastikan email karyawan ada" disabled>
                        Kirim
                      </button>
                    @endif

                    {{-- Lihat (public token) --}}
                    @if(!empty($row->view_token))
                      <a href="{{ route('my.payslip.view', $row->view_token) }}" target="_blank"
                         class="px-2 py-1 rounded-md text-xs font-semibold bg-slate-800 text-white hover:bg-slate-900">
                        Lihat
                      </a>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-6 py-10 text-center text-slate-600">
                  Belum ada data payslip untuk filter ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if(method_exists(($rows ?? null),'links'))
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200">
          {{ $rows->withQueryString()->links() }}
        </div>
      @endif
    </div>

    {{-- Bulk helper strip --}}
    <div class="mt-3 text-xs text-slate-600">
      Terpilih:
      <span id="bulk-count" class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 ring-1 ring-slate-200">0</span>
      baris. (Hanya baris berstatus <span class="font-semibold">Locked/Sent & ber-email</span> yang bisa dipilih.)
    </div>
  </div>
</div>

{{-- SweetAlert for nicer confirms (fallback to confirm() if blocked) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const $  = (q, ctx=document) => ctx.querySelector(q);
  const $$ = (q, ctx=document) => Array.from(ctx.querySelectorAll(q));

  function enabledChecks() {
    // hanya checkbox yang tidak disabled yang dihitung
    return $$('.row-check:not(:disabled)');
  }

  function setMasterState() {
    const checks = enabledChecks();
    const master = $('#check-all');
    if (!master || checks.length === 0) {
      if (master) { master.checked = false; master.indeterminate = false; master.disabled = true; }
      return;
    }
    master.disabled = false;
    const checked = checks.filter(c => c.checked).length;
    master.checked = checked === checks.length && checked > 0;
    master.indeterminate = checked > 0 && checked < checks.length;
  }

  function collectIds() {
    const ids = enabledChecks().filter(i => i.checked).map(i => i.value);
    const input = $('#bulk-ids');
    if (input) input.value = ids.join(',');
    const count = $('#bulk-count');
    if (count) count.textContent = ids.length;
    const btn = $('#bulk-send-btn');
    if (btn) btn.disabled = ids.length === 0;
  }

  function toggleAll(master) {
    enabledChecks().forEach(cb => cb.checked = master.checked);
    collectIds();
    setMasterState();
  }

  document.addEventListener('change', (e) => {
    if (e.target.id === 'check-all') toggleAll(e.target);
    if (e.target.classList.contains('row-check')) {
      collectIds();
      setMasterState();
    }
  });

  // Init states on load
  document.addEventListener('DOMContentLoaded', () => {
    setMasterState();
    collectIds();
  });

  // Confirm helpers (use SweetAlert if available)
  function confirmLock(form, name, period) {
    if (typeof Swal === 'undefined') return confirm('Kunci payslip ini? Setelah dikunci tidak bisa diubah.');
    Swal.fire({
      title: 'Kunci payslip?',
      text: `Pengguna: ${name || '-'} • Periode: ${period || '-'}`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#334155', // slate-700
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Ya, kunci',
      cancelButtonText: 'Batal',
      customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
  }

  function confirmSendOne(form, email, period) {
    if (typeof Swal === 'undefined') return confirm('Kirim payslip ini via email & link token?');
    Swal.fire({
      title: 'Kirim payslip?',
      text: `Email: ${email || '-'} • Periode: ${period || '-'}`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#059669', // emerald-600
      cancelButtonColor: '#0284c7',  // sky-600
      confirmButtonText: 'Kirim',
      cancelButtonText: 'Batal',
      customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
  }

  // Bulk submit confirm
  const bulkForm = $('#bulk-form');
  if (bulkForm) {
    bulkForm.addEventListener('submit', (e) => {
      const ids = ($('#bulk-ids')?.value || '').split(',').filter(Boolean);
      // Kalau tidak ada IDs, tetap lanjut: controller akan pakai period/site (Mode A)
      if (ids.length > 0) {
        if (typeof Swal !== 'undefined') {
          e.preventDefault();
          Swal.fire({
            title: 'Kirim massal?',
            text: `Akan mengirim ${ids.length} payslip sekarang.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b', // amber-500
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Kirim',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
          }).then(r => { if (r.isConfirmed) bulkForm.submit(); });
          return false;
        }
        // fallback native confirm
        if (!confirm(`Kirim ${ids.length} payslip sekarang?`)) e.preventDefault();
      }
    });
  }
</script>
@endsection
