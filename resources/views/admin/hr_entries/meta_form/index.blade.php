{{-- resources/views/admin/hr_entries/meta/index.blade.php --}}
@extends('layouts.app')
@section('title','HR Meta Form Config')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-cog" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="3"/>
      <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1-1.7 3a1.7 1.7 0 0 1-1.6.8l-2-.3a7 7 0 0 1-1.6.9l-.3 2a1.7 1.7 0 0 1-1.7 1.4h-3a1.7 1.7 0 0 1-1.7-1.4l-.3-2a7 7 0 0 1-1.6-.9l-2 .3a1.7 1.7 0 0 1-1.6-.8l-1.7-3 .1-.1A1.7 1.7 0 0 0 4.6 15a7 7 0 0 1 0-2 1.7 1.7 0 0 0-.3-1.8l-.1-.1 1.7-3a1.7 1.7 0 0 1 1.6-.8l2 .3a7 7 0 0 1 1.6-.9l.3-2A1.7 1.7 0 0 1 13 1h3a1.7 1.7 0 0 1 1.7 1.4l.3 2a7 7 0 0 1 1.6.9l2-.3a1.7 1.7 0 0 1 1.6.8l1.7 3-.1.1A1.7 1.7 0 0 0 19.4 13a7 7 0 0 1 0 2z"/>
    </g>
  </symbol>
  <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M11 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7" stroke-width="2" stroke-linecap="round"/>
    <path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
</svg>

<div class="max-w-6xl mx-auto space-y-8">
  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-cog"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">HR Meta Form Config</h1>
          <p class="text-white/85 text-sm">Atur field dinamis per jenis entry HR — tanpa JSON di UI.</p>
        </div>
      </div>

      <a href="{{ route('admin.hr-entries.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-arrow-left"/></svg>
        Kembali
      </a>
    </div>
  </div>

  {{-- ALERTS --}}
  @if (session('success'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- LIST TYPES --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow overflow-hidden">
    <div class="px-4 md:px-6 py-4 border-b border-emerald-100 flex items-center justify-between">
      <div class="font-semibold text-slate-800">Daftar Jenis & Form Meta</div>
      <div class="text-sm text-slate-600">
        Total: <span class="font-semibold">{{ count($types ?? []) }}</span> jenis
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">Type</th>
            <th class="px-4 py-3 text-left">Key</th>
            <th class="px-4 py-3 text-left">Fields</th>
            <th class="px-4 py-3 text-left">Preview</th>
            <th class="px-4 py-3 text-right w-40">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
        @foreach($types as $key => $label)
          @php
            $cfg    = $map[$key] ?? [];
            $fields = is_array($cfg['fields'] ?? null) ? $cfg['fields'] : [];
            $count  = count($fields);
          @endphp
          <tr class="hover:bg-emerald-50/40 transition">
            <td class="px-4 py-3 font-medium text-slate-800">{{ $label }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs ring-1 ring-slate-200 bg-slate-50 text-slate-700">
                {{ $key }}
              </span>
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs ring-1 ring-emerald-200 bg-emerald-50 text-emerald-700">
                {{ $count }} field
              </span>
            </td>
            <td class="px-4 py-3 text-slate-600">
              @if($count)
                <div class="flex flex-wrap gap-1.5">
                  @foreach(array_slice($fields,0,4) as $f)
                    <span class="px-2 py-0.5 rounded-full text-[11px] ring-1 ring-slate-200 bg-slate-50 text-slate-700">
                      {{ $f['name'] ?? ($f['key'] ?? 'field') }}
                    </span>
                  @endforeach
                  @if($count > 4)
                    <span class="px-2 py-0.5 rounded-full text-[11px] ring-1 ring-slate-200 bg-slate-50 text-slate-700">
                      +{{ $count - 4 }} lagi
                    </span>
                  @endif
                </div>
              @else
                <span class="text-slate-400">—</span>
              @endif
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end">
                <a href="{{ route('admin.hr-entries.meta-form.manage', $key) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold ring-1 ring-emerald-600 hover:bg-emerald-700">
                  <svg class="w-4 h-4" aria-hidden="true"><use href="#i-edit"/></svg>
                  Kelola
                </a>
              </div>
            </td>
          </tr>
        @endforeach
        @if(empty($types) || !count($types))
          <tr>
            <td colspan="5" class="px-6 py-10">
              <div class="text-center text-slate-500">Belum ada jenis yang terdaftar.</div>
            </td>
          </tr>
        @endif
        </tbody>
      </table>
    </div>

    <div class="px-4 md:px-6 py-4 border-t border-emerald-100 bg-white text-sm text-slate-600">
      Tip: klik <span class="font-medium text-slate-800">Kelola</span> untuk menambah/menghapus field dan mengatur tipe input.
    </div>
  </div>

  {{-- FOOTER LINK --}}
  <div>
    <a href="{{ route('admin.hr-entries.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
      <svg class="w-4 h-4"><use href="#i-arrow-left"/></svg>
      Kembali ke HR Entries
    </a>
  </div>
</div>
@endsection
