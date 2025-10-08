{{-- resources/views/admin/audit/index.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('title', 'Audit Logs')

@section('content')
<div class="space-y-5" x-data="{ openJson: null }">

  {{-- HEADER (seragam hijau–biru + aksen emas) --}}
  <div class="relative overflow-hidden rounded-2xl shadow ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.8)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-4 sm:px-6 lg:px-8 py-5 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight">🧾 Audit Logs</h1>
          <p class="text-sm text-white/90">Pantau aktivitas user: login, create, update, delete, dan lainnya.</p>
        </div>

        <div class="flex items-center gap-2">
          <a href="{{ route('admin.audit.export') }}"
             class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-400 text-slate-900 text-sm font-semibold shadow ring-1 ring-amber-300/40 hover:bg-amber-300 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
            </svg>
            Export CSV
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- FLASH (opsional) --}}
  @if (session('status') || session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200">
      <div class="text-sm font-medium">{{ session('status') ?? session('success') }}</div>
    </div>
  @endif

  {{-- FILTERS (seragam kartu putih / ring) --}}
  <form method="GET" class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      {{-- Cari --}}
      <div class="md:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Cari</label>
        <div class="relative">
          <input type="text" name="q" value="{{ request('q', $q ?? '') }}" placeholder="Action / Entity / ID…"
                 class="w-full rounded-xl border-slate-300 pl-10 pr-28 py-2.5 text-sm shadow-sm focus:ring-teal-600 focus:border-teal-600">
          <button class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
            Cari
          </button>
        </div>
      </div>

      {{-- Aksi --}}
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Aksi</label>
        @php $action = request('action'); @endphp
        <select name="action"
                class="w-full rounded-xl border-slate-300 py-2.5 text-sm shadow-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">Semua</option>
          @foreach(['login','logout','division_created','division_updated','division_deleted','create','update','delete'] as $opt)
            <option value="{{ $opt }}" @selected($action === $opt)>{{ Str::headline(str_replace('_',' ',$opt)) }}</option>
          @endforeach
        </select>
      </div>

      {{-- User --}}
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">User</label>
        @php
          $userId = request('user');
          $userOptions = \App\Models\User::query()->orderBy('name')->limit(200)->get(['id','name']);
        @endphp
        <select name="user"
                class="w-full rounded-xl border-slate-300 py-2.5 text-sm shadow-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">Semua</option>
          @foreach($userOptions as $u)
            <option value="{{ $u->id }}" @selected((string)$userId === (string)$u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Reset --}}
    <div class="mt-3">
      @if(request()->filled('q') || request()->filled('action') || request()->filled('user'))
        <a href="{{ route('admin.audit.index') }}"
           class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-xl bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
          Reset filter
        </a>
      @endif
    </div>
  </form>

  {{-- TABLE (sticky header, hover serumpun) --}}
  <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
    <div class="max-h-[70vh] overflow-auto">
      <table class="w-full text-sm">
        <thead class="sticky top-0 bg-slate-50 border-b border-slate-200">
          <tr class="text-left text-slate-700">
            <th class="p-3 font-semibold w-[170px]">Waktu</th>
            <th class="p-3 font-semibold w-[200px]">User</th>
            <th class="p-3 font-semibold">Action</th>
            <th class="p-3 font-semibold">Entity</th>
            <th class="p-3 font-semibold w-[140px]">IP</th>
            <th class="p-3 font-semibold w-[90px]">Detail</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($logs as $log)
            @php
              $act = (string) $log->action;
              $badge =
                (str_contains($act, 'delete') ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' :
                (str_contains($act, 'update') ? 'bg-amber-100 text-amber-700 ring-1 ring-amber-200' :
                (str_contains($act, 'create') ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' :
                (str_contains($act, 'login') || str_contains($act,'logout') ? 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200' :
                'bg-slate-100 text-slate-700 ring-1 ring-slate-200'))));
            @endphp
            <tr class="hover:bg-emerald-50/40">
              <td class="p-3 text-slate-700">{{ $log->created_at }}</td>

              <td class="p-3">
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-emerald-600 text-white grid place-items-center text-[11px] font-bold">
                    {{ strtoupper(mb_substr($log->user->name ?? 'G', 0, 1)) }}
                  </div>
                  <div class="truncate">
                    <div class="font-medium text-slate-800 truncate">{{ $log->user->name ?? 'Guest' }}</div>
                    @if($log->user?->email)
                      <div class="text-[11px] text-slate-500 truncate">{{ $log->user->email }}</div>
                    @endif
                  </div>
                </div>
              </td>

              <td class="p-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                  {{ Str::headline(str_replace('_',' ',$log->action)) }}
                </span>
              </td>

              <td class="p-3">
                <div class="text-slate-800">
                  {{ class_basename($log->entity_type) }}
                  @if($log->entity_id)
                    <span class="text-slate-400">#{{ $log->entity_id }}</span>
                  @endif
                </div>

                @if($log->changes)
                  <button type="button"
                          class="mt-1 text-xs text-sky-700 hover:text-sky-900"
                          @click="openJson === '{{ $log->id }}' ? openJson = null : openJson = '{{ $log->id }}'">
                    Lihat perubahan
                  </button>
                  <div x-show="openJson === '{{ $log->id }}'" x-transition
                       class="mt-2 bg-slate-50 border rounded-xl p-2 text-[11px] text-slate-700 overflow-auto max-h-40">
                    <pre class="whitespace-pre-wrap">{{ json_encode($log->changes, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                  </div>
                @endif
              </td>

              <td class="p-3">
                <div class="flex items-center gap-2">
                  <code class="text-slate-800">{{ $log->ip_address ?? '—' }}</code>
                  @if($log->ip_address)
                    <button type="button"
                            class="text-xs px-2 py-0.5 rounded-lg bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-50"
                            @click="navigator.clipboard.writeText('{{ $log->ip_address }}')">
                      Copy
                    </button>
                  @endif
                </div>
              </td>

              <td class="p-3">
                <a href="{{ route('admin.audit.show', $log->id) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                  Detail
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="p-10">
                <div class="text-center">
                  <div class="mx-auto h-12 w-12 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 grid place-items-center shadow-sm">
                    <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 6h.01M4 6h16v12H4z"/>
                    </svg>
                  </div>
                  <h3 class="mt-3 font-semibold text-slate-800">Tidak ada data audit</h3>
                  <p class="text-sm text-slate-500 mt-1">Aktivitas akan tampil di sini saat ada interaksi.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 py-4 border-t bg-slate-50">
      {{ $logs->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
