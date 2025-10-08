{{-- resources/views/admin/master/index.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('title','Master: ' . Str::headline($entity))

{{-- ===== PAGE HEADER (seragam: gradient teal→sky + aksen gold) ===== --}}
@section('header')
  <div class="relative overflow-hidden rounded-2xl shadow ring-1 ring-teal-900/20">
    <div class="absolute inset-0 bg-gradient-to-r from-teal-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(90%_90%_at_12%_10%,_rgba(255,255,255,.9)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-20 -top-14 h-40 w-40 rounded-full bg-amber-300/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center rounded-full bg-white/15 text-white px-3 py-1 text-[11px] font-semibold ring-1 ring-white/30">
          MASTER
        </span>
        <h2 class="font-extrabold text-2xl sm:text-[28px] tracking-tight">
          Master — {{ Str::headline($entity) }}
        </h2>
      </div>

      <div class="flex items-center gap-2">
        @if (Route::has('admin.master.overview'))
          <a href="{{ route('admin.master.overview') }}"
             class="px-4 py-2 rounded-xl bg-white/10 text-white ring-1 ring-white/30 hover:bg-white/15 text-sm font-semibold transition">
            Overview
          </a>
        @endif
        <a href="{{ route('admin.master.create', $entity) }}"
           class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 text-sm shadow-md ring-1 ring-emerald-700/20 transition">
          + Create
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  {{-- FLASH STATUS --}}
  @if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200">
      <div class="text-sm font-medium">{{ session('status') }}</div>
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-700 ring-1 ring-red-200">
      <div class="text-sm font-semibold mb-1">Gagal:</div>
      <ul class="text-sm list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- ===== TOOLBAR (serumpun UI) ===== --}}
  <div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <form method="GET" class="w-full md:max-w-xl">
        <div class="flex gap-2">
          <input type="text" name="q" value="{{ $search }}" placeholder="Cari name/code/description..."
                 class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:ring-teal-600">
          <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
            Search
          </button>
        </div>
      </form>

      <div class="flex items-center gap-2">
        @if (Route::has('admin.master.import.template'))
          <a href="{{ route('admin.master.import.template', $entity) }}"
             class="px-3 py-2 rounded-xl text-sm bg-white ring-1 ring-slate-200 hover:bg-slate-50">
            Template
          </a>
        @endif
        @if (Route::has('admin.master.export'))
          <a href="{{ route('admin.master.export', $entity) }}"
             class="px-3 py-2 rounded-xl text-sm bg-white ring-1 ring-slate-200 hover:bg-slate-50">
            Export CSV
          </a>
        @endif
        @if (Route::has('admin.master.import'))
          <form method="POST" action="{{ route('admin.master.import', $entity) }}" enctype="multipart/form-data" class="flex items-center">
            @csrf
            <label class="text-sm px-3 py-2 rounded-xl bg-white ring-1 ring-slate-200 hover:bg-slate-50 cursor-pointer">
              <input type="file" name="file" accept=".csv" class="hidden" onchange="this.form.submit()">
              Import CSV
            </label>
          </form>
        @endif
      </div>
    </div>

    {{-- ===== TABLE (aksi & warna konsisten: tombol hijau -> dropdown) ===== --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-50 text-left text-slate-600 border-b border-slate-200">
            <th class="px-4 py-3 w-56">Name</th>
            <th class="px-4 py-3 w-40">Code</th>
            <th class="px-4 py-3">Description</th>
            <th class="px-4 py-3 w-40">Updated</th>
            <th class="px-4 py-3 w-40 text-center">Actions</th>
          </tr>
        </thead>
        <tbody x-data class="[&>tr:nth-child(even)]:bg-slate-50/40">
          @forelse($records as $r)
            <tr class="border-t">
              {{-- NAME → SHOW --}}
              <td class="px-4 py-3 font-medium">
                <a href="{{ route('admin.master.show', ['entity'=>$entity,'record'=>$r->id]) }}"
                   class="text-slate-800 hover:underline">
                  {{ $r->name }}
                </a>
              </td>
              <td class="px-4 py-3 text-slate-700">{{ $r->code ?? '—' }}</td>
              <td class="px-4 py-3 text-slate-600">
                <span title="{{ $r->description }}">{{ Str::limit((string) $r->description, 120) ?: '—' }}</span>
              </td>
              <td class="px-4 py-3 text-slate-500">
                {{ $r->updated_at }}
              </td>

              {{-- ACTIONS (seragam: green button → pop menu) --}}
              <td class="px-4 py-3">
                <div x-data="{open:false}" class="relative flex items-center justify-center">
                  <button @click="open=!open" @keydown.escape.window="open=false" type="button"
                          class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold
                                 bg-emerald-600 text-white shadow hover:bg-emerald-700 ring-1 ring-emerald-700/20 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6h.01M12 12h.01M12 18h.01"/>
                    </svg>
                    Actions
                    <svg class="h-4 w-4 -mr-0.5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                    </svg>
                  </button>

                  <div x-cloak x-show="open" @click.outside="open=false" x-transition.origin.top.right
                       class="absolute right-0 top-9 w-44 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 overflow-hidden z-20">
                    <a href="{{ route('admin.master.show', ['entity'=>$entity,'record'=>$r->id]) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">
                      <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-4.55a1.5 1.5 0 10-2.12-2.12L12.88 7.88M5 19l4.55-4.55m0 0L19 5m-9.45 9.45L10 15l-5 5"/>
                      </svg>
                      Show
                    </a>
                    <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$r->id]) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">
                      <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                      </svg>
                      Edit
                    </a>
                    @if (Route::has('admin.master.permissions'))
                    <a href="{{ route('admin.master.permissions', ['entity'=>$entity,'record'=>$r->id]) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">
                      <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.567 3-3.5S13.657 4 12 4 9 5.567 9 7.5 10.343 11 12 11zm0 2c-2.761 0-5 2.015-5 4.5V20h10v-2.5c0-2.485-2.239-4.5-5-4.5z"/>
                      </svg>
                      Permissions
                    </a>
                    @endif
                    <form method="POST" action="{{ route('admin.master.destroy', ['entity'=>$entity,'record'=>$r->id]) }}" onsubmit="return confirm('Hapus data ini?')">
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
              <td colspan="5" class="px-4 py-10 text-center text-slate-600">
                Belum ada data. <a class="text-emerald-700 underline font-medium" href="{{ route('admin.master.create',$entity) }}">Buat sekarang</a>.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">
    {{ $records->links() }}
  </div>
@endsection
