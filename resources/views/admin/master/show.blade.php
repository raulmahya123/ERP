{{-- resources/views/admin/master/show.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('title', 'Detail ' . Str::headline($entity))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- ACTIONS BAR --}}
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <div class="h-8 w-8 rounded-xl bg-emerald-100 text-emerald-700 grid place-items-center">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
      </div>
      <h2 class="text-xl font-bold text-slate-800">
        Master {{ Str::headline($entity) }}
        <span class="text-slate-500 font-normal">— Detail</span>
      </h2>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('admin.master.index',$entity) }}"
         class="px-3 py-2 rounded-xl text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50">
        Back
      </a>
      <a href="{{ route('admin.master.edit',['entity'=>$entity,'record'=>$record->id]) }}"
         class="px-3 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 shadow-sm">
        Edit
      </a>
      <a href="{{ route('admin.master.permissions',['entity'=>$entity,'record'=>$record->id]) }}"
         class="px-3 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-500 shadow-sm">
        Permissions
      </a>
      <form method="POST" action="{{ route('admin.master.duplicate',['entity'=>$entity,'record'=>$record->id]) }}"
            onsubmit="return confirm('Duplicate record ini?')" class="inline">
        @csrf
        <button class="px-3 py-2 rounded-xl text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 shadow-sm">
          Duplicate
        </button>
      </form>
      <form method="POST" action="{{ route('admin.master.destroy',['entity'=>$entity,'record'=>$record->id]) }}"
            onsubmit="return confirm('Hapus record ini?')" class="inline">
        @csrf @method('DELETE')
        <button class="px-3 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 shadow-sm">
          Delete
        </button>
      </form>
    </div>
  </div>

  {{-- MAIN CONTENT --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Kartu Informasi Utama --}}
    <div class="lg:col-span-2 bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-6 py-4 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700 text-white">
        <div class="font-bold leading-tight">Master {{ Str::headline($entity) }}</div>
        <div class="text-xs text-white/85">Record Information</div>
      </div>

      <div class="p-6 space-y-4">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <dt class="text-xs text-slate-500">Name</dt>
            <dd class="text-base font-semibold text-slate-800">{{ $record->name }}</dd>
          </div>
          <div>
            <dt class="text-xs text-slate-500">Code</dt>
            <dd class="text-base text-slate-800">{{ $record->code ?? '—' }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-xs text-slate-500">Description</dt>
            <dd class="text-slate-700 whitespace-pre-line">{{ $record->description ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs text-slate-500">ID</dt>
            <dd class="text-slate-700">{{ $record->id }}</dd>
          </div>
          <div>
            <dt class="text-xs text-slate-500">Created / Updated</dt>
            <dd class="text-slate-700">
              {{ $record->created_at }} <span class="text-slate-400">→</span> {{ $record->updated_at }}
            </dd>
          </div>
          @if(property_exists($record,'created_by') && $record->created_by)
            <div>
              <dt class="text-xs text-slate-500">Created By</dt>
              <dd class="text-slate-700">{{ $record->created_by }}</dd>
            </div>
          @endif
        </dl>
      </div>
    </div>

    {{-- Kartu EXTRA (JSON) --}}
    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-6 py-4 bg-gradient-to-r from-sky-600 to-indigo-700 text-white">
        <div class="font-bold leading-tight">Extra (JSON)</div>
        <div class="text-xs text-white/85">Custom Attributes</div>
      </div>

      <div class="p-5">
        @if(!empty($extraArray) && is_array($extraArray))
          <div class="mb-4">
            <div class="text-xs text-slate-500 mb-1">Parsed</div>
            <div class="border border-slate-200 rounded-xl overflow-hidden">
              <table class="min-w-full text-sm">
                <tbody>
                  @foreach($extraArray as $k => $v)
                    <tr class="border-b last:border-0">
                      <th class="px-3 py-2 text-left font-semibold text-slate-700 w-40 align-top bg-slate-50">{{ Str::headline($k) }}</th>
                      <td class="px-3 py-2 text-slate-700">
                        @if(is_array($v) || is_object($v))
                          <pre class="text-xs bg-slate-50 p-2 rounded">{{ json_encode($v, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                          {{ (string) $v }}
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @else
          <div class="text-sm text-slate-500">Tidak ada data extra.</div>
        @endif

        {{-- RAW JSON --}}
        @php
          $pretty = $record->extra
            ? json_encode(json_decode($record->extra, true), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
            : '';
        @endphp
        @if($pretty)
          <div class="mt-4">
            <div class="text-xs text-slate-500 mb-1">Raw</div>
            <pre class="text-xs bg-slate-50 p-3 rounded overflow-x-auto"><code>{{ $pretty }}</code></pre>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
