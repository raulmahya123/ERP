{{-- resources/views/admin/hse/environmental_samples/index.blade.php --}}
@extends('layouts.app')

@section('title','HSE — Environmental Samples')

@section('content')
@php
  use Illuminate\Support\Facades\Route;

  $statusMap = [
    'draft'     => ['label' => 'Draft',     'cls' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200'],
    'submitted' => ['label' => 'Submitted', 'cls' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200'],
    'verified'  => ['label' => 'Verified',  'cls' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'],
  ];

  $typeOptions = ['air' => 'Air', 'emission' => 'Emission', 'noise' => 'Noise'];
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">HSE — Environmental Samples</h1>
            <p class="text-white/90 text-sm mt-1">Daftar sampel lingkungan (air, emisi, kebisingan).</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total:
              {{ method_exists($items,'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset

          @if(Route::has('admin.hse.environmental-samples.create'))
            @can('create', \App\Models\EnvironmentalSample::class)
              <a href="{{ route('admin.hse.environmental-samples.create') }}"
                 class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New
              </a>
            @endcan
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" class="grid gap-3 lg:grid-cols-7" autocomplete="off">
      {{-- Search --}}
      <div class="relative lg:col-span-2">
        <input type="text" name="q" value="{{ old('q', request('q')) }}" placeholder="Cari code/parameter/lokasi/metode…"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-24 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
        </svg>
        <button class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-sky-700 text-white ring-1 ring-white/30 hover:bg-sky-600 transition" type="submit">
          Cari
        </button>
      </div>

      {{-- Site (opsional) --}}
      @isset($sites)
        <div>
          <select name="site_id" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
            <option value="">All Sites</option>
            @foreach($sites as $site)
              <option value="{{ $site->id }}" @selected(request('site_id')===$site->id)>
                {{ $site->code ? $site->code.' — ' : '' }}{{ $site->name }}
              </option>
            @endforeach
          </select>
        </div>
      @endisset

      {{-- Type --}}
      <div>
        <select name="type" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          <option value="">All Types</option>
          @foreach($typeOptions as $val => $label)
            <option value="{{ $val }}" @selected(request('type')===$val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      {{-- Status --}}
      <div>
        <select name="status" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          <option value="">All Statuses</option>
          @foreach(array_keys($statusMap) as $st)
            <option value="{{ $st }}" @selected(request('status')===$st)>{{ $statusMap[$st]['label'] }}</option>
          @endforeach
        </select>
      </div>

      {{-- From --}}
      <div>
        <input type="date" name="from" value="{{ old('from', request('from')) }}"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600"
               placeholder="From" />
      </div>

      {{-- To --}}
      <div>
        <input type="date" name="to" value="{{ old('to', request('to')) }}"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600"
               placeholder="To" />
      </div>

      {{-- Reset --}}
      <div class="flex items-center gap-2">
        @if(request()->hasAny(['q','site_id','type','status','from','to']))
          <a href="{{ route('admin.hse.environmental-samples.index') }}"
             class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 w-full text-center">
            Reset
          </a>
        @endif
      </div>
    </form>
  </div>

  {{-- FLASH --}}  @if ($errors->any())
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- TABLE --}}
  <div class="p-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200">
            <tr>
              <th scope="col" class="px-4 py-3 text-left font-semibold">Code</th>
              <th scope="col" class="px-4 py-3 text-left font-semibold">Site</th>
              <th scope="col" class="px-4 py-3 text-left font-semibold">Type</th>
              <th scope="col" class="px-4 py-3 text-left font-semibold">Location</th>
              <th scope="col" class="px-4 py-3 text-left font-semibold">Collected At</th>
              <th scope="col" class="px-4 py-3 text-left font-semibold">Status</th>
              <th scope="col" class="px-4 py-3 text-center font-semibold w-44">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($items ?? [] as $s)
              @php
                $st    = $s->status ?? 'draft';
                $badge = $statusMap[$st] ?? $statusMap['draft'];
              @endphp
              <tr class="hover:bg-emerald-50/40">
                <td class="px-4 py-3 font-mono text-emerald-700">{{ $s->code ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-900">
                  {{ optional($s->site)->code ? optional($s->site)->code.' — ' : '' }}{{ optional($s->site)->name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-slate-900">{{ strtoupper($s->type ?? '—') }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $s->location ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-700">{{ optional($s->sampled_at)->format('Y-m-d H:i') ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge['cls'] }}">
                    {{ $badge['label'] }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    @can('view', $s)
                      @if(Route::has('admin.hse.environmental-samples.show'))
                        <a href="{{ route('admin.hse.environmental-samples.show', $s) }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
                          Detail
                        </a>
                      @endif
                    @endcan

                    @can('update', $s)
                      @if(Route::has('admin.hse.environmental-samples.edit'))
                        <a href="{{ route('admin.hse.environmental-samples.edit', $s) }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">
                          Edit
                        </a>
                      @endif
                    @endcan

                    @can('delete', $s)
                      <button type="button"
                              onclick="confirmDeleteSample(this)"
                              data-id="{{ $s->id }}"
                              data-code="{{ e($s->code ?? $s->id) }}"
                              class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100"
                              aria-label="Hapus sample {{ $s->code ?? $s->id }}">
                        Hapus
                      </button>
                      @if(Route::has('admin.hse.environmental-samples.destroy'))
                        <form id="del-sample-{{ $s->id }}" action="{{ route('admin.hse.environmental-samples.destroy', $s) }}" method="POST" class="hidden">
                          @csrf
                          @method('DELETE')
                        </form>
                      @endif
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center gap-2 text-slate-600">
                    <svg class="h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <circle cx="11" cy="11" r="7" stroke-width="1.7"></circle>
                      <path d="m20 20-3.5-3.5" stroke-width="1.7" stroke-linecap="round"></path>
                    </svg>
                    <div class="text-sm">Belum ada data sampel.</div>
                    @if(Route::has('admin.hse.environmental-samples.create'))
                      @can('create', \App\Models\EnvironmentalSample::class)
                        <a href="{{ route('admin.hse.environmental-samples.create') }}"
                           class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 underline">
                          Tambah sekarang
                        </a>
                      @endcan
                    @endif
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @isset($items)
        <div class="px-4 py-4 border-t bg-slate-50">
          {{ $items->withQueryString()->onEachSide(1)->links() }}
        </div>
      @endisset
    </div>
  </div>
</div>
@endsection

@push('scripts')
  @once

  @endonce
  <script>
    function confirmDeleteSample(el){
      const id   = el.dataset.id;
      const code = el.dataset.code || '';
      if (!id) return;

      if (typeof Swal === 'undefined') {
        if (confirm('Hapus sample: ' + code + ' ?')) {
          const f = document.getElementById('del-sample-' + id);
          if (f) f.submit();
        }
        return;
      }

      Swal.fire({
        title: 'Hapus Sample?',
        text: 'Apakah kamu yakin ingin menghapus sample: ' + code + ' ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#0284c7',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-lg px-4 py-2 font-semibold',
          cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
      }).then((r)=>{ if(r.isConfirmed){
          const f = document.getElementById('del-sample-' + id);
          if (f) f.submit();
      }});
    }
  </script>
@endpush
