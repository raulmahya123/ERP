@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="p-6 space-y-5" x-data="{ openJson: null }">
  {{-- HEADER / TOOLS --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">Audit Logs</h1>
      <p class="text-sm text-slate-500">Pantau aktivitas user: login, create, update, delete, dan lainnya.</p>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.audit.export') }}"
         class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-emerald-600 text-white text-sm hover:bg-emerald-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
        </svg>
        Export CSV
      </a>
    </div>
  </div>

  {{-- FILTERS --}}
  <form method="GET" class="bg-white border rounded-xl p-4 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Cari</label>
        <div class="flex">
          <input type="text" name="q" value="{{ $q }}" placeholder="Action / Entity / ID…"
                 class="w-full border rounded-l-md px-3 py-2 text-sm focus:outline-none focus:ring focus:border-indigo-300">
          <button class="px-3 py-2 rounded-r-md bg-indigo-600 text-white text-sm hover:bg-indigo-700">Cari</button>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Aksi</label>
        @php $action = request('action'); @endphp
        <select name="action" class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:border-indigo-300">
          <option value="">Semua</option>
          @foreach(['login','logout','division_created','division_updated','division_deleted','create','update','delete'] as $opt)
            <option value="{{ $opt }}" @selected($action === $opt)>{{ Str::headline(str_replace('_',' ',$opt)) }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">User</label>
        @php
          $userId = request('user');
          $userOptions = \App\Models\User::query()->orderBy('name')->limit(200)->get(['id','name']);
        @endphp
        <select name="user" class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:border-indigo-300">
          <option value="">Semua</option>
          @foreach($userOptions as $u)
            <option value="{{ $u->id }}" @selected($userId === $u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="mt-3 flex items-center gap-2">
      @if($q || $action || $userId)
        <a href="{{ route('admin.audit.index') }}"
           class="text-xs px-2.5 py-1.5 rounded-md bg-slate-100 text-slate-600 hover:bg-slate-200">Reset</a>
      @endif
    </div>
  </form>

  {{-- TABLE --}}
  <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="max-h-[70vh] overflow-auto">
      <table class="w-full text-sm">
        <thead class="sticky top-0 bg-slate-50 border-b">
          <tr class="text-left text-slate-600">
            <th class="p-3 font-semibold w-[170px]">Waktu</th>
            <th class="p-3 font-semibold w-[200px]">User</th>
            <th class="p-3 font-semibold">Action</th>
            <th class="p-3 font-semibold">Entity</th>
            <th class="p-3 font-semibold w-[140px]">IP</th>
            <th class="p-3 font-semibold w-[90px]">Detail</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($logs as $log)
            @php
              $action = (string) $log->action;
              $badge =
                str_contains($action, 'delete') ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' :
                (str_contains($action, 'update') ? 'bg-amber-100 text-amber-700 ring-1 ring-amber-200' :
                (str_contains($action, 'create') ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' :
                (str_contains($action, 'login') ? 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200' :
                'bg-slate-100 text-slate-700 ring-1 ring-slate-200')));
            @endphp
            <tr class="hover:bg-slate-50/60">
              <td class="p-3 text-slate-700">{{ $log->created_at }}</td>
              <td class="p-3">
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-emerald-600 text-white grid place-items-center text-xs font-bold">
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
                          class="mt-1 text-xs text-indigo-600 hover:text-indigo-800"
                          @click="openJson === '{{ $log->id }}' ? openJson = null : openJson = '{{ $log->id }}'">
                    {{ __('Lihat perubahan') }}
                  </button>
                  <div x-show="openJson === '{{ $log->id }}'" x-transition
                       class="mt-2 bg-slate-50 border rounded p-2 text-[11px] text-slate-700 overflow-auto max-h-40">
                    <pre class="whitespace-pre-wrap">{{ json_encode($log->changes, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                  </div>
                @endif
              </td>
              <td class="p-3">
                <div class="flex items-center gap-2">
                  <code class="text-slate-700">{{ $log->ip_address ?? '—' }}</code>
                  @if($log->ip_address)
                    <button type="button" class="text-xs text-slate-500 hover:text-slate-700"
                            @click="navigator.clipboard.writeText('{{ $log->ip_address }}')">Copy</button>
                  @endif
                </div>
              </td>
              <td class="p-3">
                <a href="{{ route('admin.audit.show', $log->id) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md border text-xs hover:bg-slate-50">
                  Detail
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="p-8 text-center text-slate-500">Tidak ada data audit.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 py-3 border-t bg-slate-50">
      {{ $logs->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
