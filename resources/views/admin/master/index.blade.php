{{-- resources/views/admin/master/index.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('title', 'Master — ' . Str::headline($entity))

@section('content')
<style>[x-cloak]{display:none}</style>

<div class="max-w-7xl mx-auto space-y-6">

  {{-- HERO / PAGE TITLE (serumpun hijau-emas-biru) --}}
  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-start gap-3">
        <div class="h-11 w-11 rounded-2xl bg-white/10 text-white grid place-items-center ring-1 ring-white/20 shadow-sm">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
          </svg>
        </div>
        <div>
          <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Master — {{ Str::headline($entity) }}
          </h2>
          <p class="text-white/90 text-sm">Manajemen data master untuk entitas ini.</p>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        @if (Route::has('admin.master.overview'))
          <a href="{{ route('admin.master.overview') }}"
             class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 text-white hover:bg-white/15 transition">
            Overview
          </a>
        @endif
        <a href="{{ route('admin.master.create', $entity) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-amber-400 to-yellow-500 shadow hover:from-amber-300 hover:to-yellow-400 ring-1 ring-amber-300/40 transition">
          + Create
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @if (session('status'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('status') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-4 py-3 rounded-xl bg-red-50 text-red-700 ring-1 ring-red-200 text-sm">
      <div class="font-semibold mb-1">Gagal:</div>
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- TOOLBAR + TABLE WRAPPER --}}
  <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
    {{-- TOOLBAR --}}
    <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        {{-- Search --}}
        <form method="GET" class="w-full md:max-w-md">
          <label class="sr-only" for="q">Search</label>
          <div class="flex gap-2">
            <input id="q" type="text" name="q" value="{{ $search }}" placeholder="Cari name/code/description..."
                   class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
            >
            <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white text-sm font-semibold shadow-sm hover:from-emerald-700 hover:to-teal-800 transition">
              Search
            </button>
          </div>
        </form>

        {{-- Tools --}}
        <div class="flex flex-wrap items-center gap-2">
          @if (Route::has('admin.master.import.template'))
            <a href="{{ route('admin.master.import.template', $entity) }}"
               class="px-3 py-2 rounded-xl text-sm bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
              Template
            </a>
          @endif
          @if (Route::has('admin.master.export'))
            <a href="{{ route('admin.master.export', $entity) }}"
               class="px-3 py-2 rounded-xl text-sm bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
              Export CSV
            </a>
          @endif
          @if (Route::has('admin.master.import'))
            <form method="POST" action="{{ route('admin.master.import', $entity) }}" enctype="multipart/form-data" class="flex items-center">
              @csrf
              <label class="text-sm px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 cursor-pointer transition">
                <input type="file" name="file" accept=".csv" class="hidden" onchange="this.form.submit()">
                Import CSV
              </label>
            </form>
          @endif
        </div>
      </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="sticky top-0 z-10">
          <tr class="bg-slate-50 text-left text-slate-600 border-b border-slate-200">
            <th class="px-4 py-3 w-56">Name</th>
            <th class="px-4 py-3 w-40">Code</th>
            <th class="px-4 py-3">Description</th>
            <th class="px-4 py-3 w-40">Updated</th>
            <th class="px-4 py-3 w-40 text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="[&>tr:nth-child(even)]:bg-slate-50/40">
          @forelse($records as $r)
            <tr class="border-t hover:bg-emerald-50/50 transition">
              <td class="px-4 py-3 font-medium">
                <a href="{{ route('admin.master.show', ['entity'=>$entity,'record'=>$r->id]) }}"
                   class="text-slate-800 hover:text-emerald-700 hover:underline">
                  {{ $r->name }}
                </a>
              </td>
              <td class="px-4 py-3 text-slate-700">
                @if($r->code)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs ring-1 ring-sky-200 bg-sky-50 text-sky-800">
                    <i class="size-1.5 rounded-full bg-sky-500"></i>{{ $r->code }}
                  </span>
                @else
                  —
                @endif
              </td>
              <td class="px-4 py-3 text-slate-600">
                <span title="{{ $r->description }}">{{ Str::limit((string) $r->description, 120) ?: '—' }}</span>
              </td>
              <td class="px-4 py-3 text-slate-500">
                {{ $r->updated_at ? \Illuminate\Support\Carbon::parse($r->updated_at)->format('Y-m-d H:i') : '—' }}
              </td>

              {{-- ACTIONS DROPDOWN --}}
              <td class="px-4 py-3 text-center">
                <div x-data="{open:false}" class="relative inline-block">
                  <button @click="open=!open" @keydown.escape.window="open=false" type="button"
                          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                                 bg-gradient-to-r from-emerald-600 to-teal-700 text-white hover:from-emerald-700 hover:to-teal-800 shadow-sm transition">
                    Actions
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                    </svg>
                  </button>

                  {{-- DROPDOWN MENU --}}
                  <div x-cloak x-show="open" @click.outside="open=false" x-transition.origin.top.right
                       class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 overflow-hidden z-20">
                    <a href="{{ route('admin.master.show', ['entity'=>$entity,'record'=>$r->id]) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-emerald-50/60">
                      <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-4.55a1.5 1.5 0 10-2.12-2.12L12.88 7.88M5 19l4.55-4.55m0 0L19 5m-9.45 9.45L10 15l-5 5"/>
                      </svg>
                      Show
                    </a>
                    <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$r->id]) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-sky-50/70">
                      <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                      </svg>
                      Edit
                    </a>
                    @if (Route::has('admin.master.permissions'))
                      <a href="{{ route('admin.master.permissions', ['entity'=>$entity,'record'=>$r->id]) }}"
                         class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-amber-50/70">
                        <svg class="h-4 w-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.567 3-3.5S13.657 4 12 4 9 5.567 9 7.5 10.343 11 12 11zm0 2c-2.761 0-5 2.015-5 4.5V20h10v-2.5c0-2.485-2.239-4.5-5-4.5z"/>
                        </svg>
                        Permissions
                      </a>
                    @endif
                    <form method="POST" action="{{ route('admin.master.destroy', ['entity'=>$entity,'record'=>$r->id]) }}"
                          onsubmit="return confirm('Hapus data ini?')" class="border-t border-slate-100">
                      @csrf @method('DELETE')
                      <button class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7v10m6-10v10M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12M10 7V5a2 2 0 012-2h0a2 2 0 012 2v2"/>
                        </svg>
                        Delete
                      </button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-12">
                <div class="text-center">
                  <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center">
                    <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                    </svg>
                  </div>
                  <p class="mt-3 text-slate-700 font-medium">Belum ada data</p>
                  <a href="{{ route('admin.master.create', $entity) }}"
                     class="inline-flex mt-2 items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold
                            bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20 shadow">
                    + Buat sekarang
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- PAGINATION (seragam) --}}
  <div class="mt-4">
    {{ $records->onEachSide(1)->links() }}
  </div>
</div>
@endsection
