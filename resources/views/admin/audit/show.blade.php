@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('content')
<div class="p-6 space-y-6" x-data="{ showUA:false, showJson:true }" x-cloak>

  {{-- HEADER STRIP --}}
  <div class="rounded-2xl overflow-hidden border shadow-sm">
    <div class="bg-gradient-to-r from-green-700 via-blue-700 to-yellow-600 px-6 py-5">
      <div class="text-xs text-white/80 flex items-center gap-1">
        <a href="{{ route('admin.audit.index') }}" class="hover:text-white">Audit Logs</a>
        <span>/</span><span class="text-white">Detail</span>
      </div>
      <div class="mt-1 flex items-end justify-between gap-3">
        <div>
          <h1 class="text-2xl font-bold text-white tracking-tight">Detail Audit Log</h1>
          <p class="text-sm text-white/90 mt-1">
            ID:
            <code class="ml-1 rounded bg-white/15 px-2 py-0.5 text-white/95">{{ $log->id }}</code>
          </p>
        </div>
        <div class="hidden sm:flex items-center gap-2">
          <a href="{{ route('admin.audit.export') }}"
             class="inline-flex items-center gap-2 rounded-lg text-sm text-white px-3 py-2
                    bg-gradient-to-r from-yellow-600 to-yellow-500 hover:from-yellow-700 hover:to-yellow-600 shadow-sm">
            Export CSV
          </a>
        </div>
      </div>
    </div>
    <div class="bg-white px-6 py-3 flex items-center justify-between">
      <a href="{{ route('admin.audit.index') }}"
         class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
        ← Kembali
      </a>
      <a href="{{ route('admin.audit.export') }}"
         class="sm:hidden inline-flex items-center gap-2 rounded-lg text-sm text-white px-3 py-2
                bg-gradient-to-r from-yellow-600 to-yellow-500 hover:from-yellow-700 hover:to-yellow-600">
        Export CSV
      </a>
    </div>
  </div>

  {{-- KONTEN --}}
  <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 bg-slate-100/60 gap-px">

      {{-- INFO --}}
      <section class="bg-white p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Info</h2>
        <dl class="text-sm space-y-4">
          <div class="flex">
            <dt class="w-40 shrink-0 text-slate-500">Waktu</dt>
            <dd class="text-slate-800">
              {{ optional($log->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i:s') ?? '—' }}
            </dd>
          </div>

          <div class="flex">
            <dt class="w-40 shrink-0 text-slate-500">User</dt>
            <dd class="text-slate-800">
              <span class="inline-flex items-center gap-2">
                @php
                  $avatarLetter = strtoupper(mb_substr($log->user->name ?? 'G', 0, 1));
                @endphp
                <span class="w-7 h-7 rounded-full grid place-items-center text-white text-xs font-bold
                             bg-gradient-to-br from-green-600 to-green-500 shadow-sm">
                  {{ $avatarLetter }}
                </span>
                <span class="font-medium">{{ $log->user->name ?? 'Guest' }}</span>
                @if($log->user?->email)
                  <span class="text-slate-400">— {{ $log->user->email }}</span>
                @endif
              </span>
            </dd>
          </div>

          <div class="flex">
            <dt class="w-40 shrink-0 text-slate-500">Action</dt>
            @php
              $action = (string) ($log->action ?? '');
              $badge =
                (str_contains($action,'delete') ? 'bg-rose-100 text-rose-800 ring-1 ring-rose-200' :
                (str_contains($action,'update') ? 'bg-yellow-100 text-yellow-900 ring-1 ring-yellow-300' :
                (str_contains($action,'create') ? 'bg-green-100 text-green-800 ring-1 ring-green-300' :
                (str_contains($action,'login')  ? 'bg-blue-100 text-blue-800 ring-1 ring-blue-300'
                                                : 'bg-slate-100 text-slate-800 ring-1 ring-slate-200'))));
            @endphp
            <dd class="text-slate-800">
              {{-- pakai FQN supaya tidak perlu `use Illuminate\Support\Str` di Blade --}}
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                {{ \Illuminate\Support\Str::headline(str_replace('_',' ', $action)) }}
              </span>
            </dd>
          </div>

          <div class="flex">
            <dt class="w-40 shrink-0 text-slate-500">Entity</dt>
            <dd class="text-slate-800">
              <span class="inline-flex items-center gap-2">
                <span class="font-medium">{{ $log->entity_type ? class_basename($log->entity_type) : '—' }}</span>
                @if($log->entity_id)
                  <span class="text-slate-400">#{{ $log->entity_id }}</span>
                  <button type="button"
                          class="text-xs text-blue-700 hover:text-blue-900"
                          @click="navigator.clipboard?.writeText('{{ $log->entity_id }}')">
                    Copy ID
                  </button>
                @endif
              </span>
            </dd>
          </div>

          <div class="flex">
            <dt class="w-40 shrink-0 text-slate-500">IP Address</dt>
            <dd class="text-slate-800">
              <code class="rounded border bg-slate-50 px-1.5 py-0.5">{{ $log->ip_address ?? '—' }}</code>
              @if($log->ip_address)
                <button type="button"
                        class="ml-2 text-xs text-blue-700 hover:text-blue-900"
                        @click="navigator.clipboard?.writeText('{{ $log->ip_address }}')">
                  Copy
                </button>
              @endif
            </dd>
          </div>

          <div class="flex">
            <dt class="w-40 shrink-0 text-slate-500">User Agent</dt>
            <dd class="text-slate-800">
              <button type="button"
                      class="text-xs rounded px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100"
                      @click="showUA = !showUA"
                      x-text="showUA ? 'Sembunyikan' : 'Lihat'">
              </button>
              <div x-show="showUA" x-transition
                   class="mt-2 rounded-lg border bg-gradient-to-br from-blue-50 to-green-50 p-3 text-[11px] text-slate-700">
                {{ $log->user_agent ?? '—' }}
              </div>
            </dd>
          </div>
        </dl>
      </section>

      {{-- PERUBAHAN (JSON) --}}
      <section class="bg-white p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-slate-700">Perubahan (JSON)</h2>
          @if(!empty($log->changes))
            <div class="flex items-center gap-2">
              <button type="button"
                      class="text-xs rounded px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100"
                      @click="showJson = !showJson"
                      x-text="showJson ? 'Collapse' : 'Expand'">
              </button>
              <button type="button"
                      class="text-xs rounded px-2 py-1 bg-yellow-50 text-yellow-800 hover:bg-yellow-100"
                      @click='navigator.clipboard?.writeText(@js($log->changes))'>
                Copy JSON
              </button>
            </div>
          @endif
        </div>

        @if(!empty($log->changes))
          <div x-show="showJson" x-transition
               class="mt-3 rounded-xl border bg-gradient-to-br from-slate-50 to-yellow-50 p-3 text-[12px] text-slate-800 overflow-auto max-h-[56vh]">
            <pre class="whitespace-pre-wrap leading-5 font-mono text-[12px]">
{{ json_encode($log->changes, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}
            </pre>
          </div>
        @else
          <p class="mt-3 text-sm text-slate-500">Tidak ada payload perubahan.</p>
        @endif
      </section>

    </div>
  </div>

  {{-- BOTTOM BAR --}}
  <div class="flex items-center justify-between">
    <a href="{{ url()->previous() ?: route('admin.audit.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
      ← Kembali
    </a>
    <a href="{{ route('admin.audit.export') }}"
       class="hidden md:inline-flex items-center gap-2 rounded-lg text-sm text-white px-3 py-2
              bg-gradient-to-r from-green-700 to-green-600 hover:from-green-800 hover:to-green-700">
      Export CSV
    </a>
  </div>

</div>
@endsection
