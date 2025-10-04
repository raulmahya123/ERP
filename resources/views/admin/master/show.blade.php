{{-- resources/views/admin/master/show.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('title', 'Detail ' . Str::headline($entity))

@section('header')
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <span class="inline-flex items-center rounded-full bg-[#0d2b52]/10 text-[#0d2b52] px-3 py-1 text-xs font-semibold">GM</span>
      <h2 class="font-semibold text-xl text-slate-800">
        {{ Str::headline($entity) }} — Detail
      </h2>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.master.index',$entity) }}"
         class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">Back</a>
      <a href="{{ route('admin.master.edit',['entity'=>$entity,'record'=>$record->id]) }}"
         class="px-3 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">Edit</a>
      <a href="{{ route('admin.master.permissions',['entity'=>$entity,'record'=>$record->id]) }}"
         class="px-3 py-2 rounded-lg text-sm font-semibold bg-amber-500 text-white hover:bg-amber-600">Permissions</a>
      <form method="POST" action="{{ route('admin.master.duplicate',['entity'=>$entity,'record'=>$record->id]) }}"
            onsubmit="return confirm('Duplicate record ini?')" class="inline">
        @csrf
        <button class="px-3 py-2 rounded-lg text-sm font-semibold bg-sky-600 text-white hover:bg-sky-700">Duplicate</button>
      </form>
      <form method="POST" action="{{ route('admin.master.destroy',['entity'=>$entity,'record'=>$record->id]) }}"
            onsubmit="return confirm('Hapus record ini?')" class="inline">
        @csrf @method('DELETE')
        <button class="px-3 py-2 rounded-lg text-sm font-semibold bg-red-600 text-white hover:bg-red-700">Delete</button>
      </form>
    </div>
  </div>
@endsection

@section('content')
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Kartu informasi utama --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b bg-gradient-to-r from-emerald-500 to-teal-700 text-white">
        <div class="font-bold">Master {{ Str::headline($entity) }}</div>
        <div class="text-xs opacity-90">Record Information</div>
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
    <div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b bg-gradient-to-r from-sky-500 to-indigo-700 text-white">
        <div class="font-bold">Extra (JSON)</div>
        <div class="text-xs opacity-90">Custom attributes</div>
      </div>

      <div class="p-4">
        @if(!empty($extraArray) && is_array($extraArray))
          {{-- Render key-value --}}
          <div class="mb-3">
            <div class="text-xs text-slate-500 mb-1">Parsed</div>
            <div class="border rounded-lg overflow-hidden">
              <table class="min-w-full text-sm">
                <tbody>
                  @foreach($extraArray as $k => $v)
                    <tr class="border-b last:border-0">
                      <th class="px-3 py-2 text-left font-semibold text-slate-700 w-40 align-top">{{ Str::headline($k) }}</th>
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

        {{-- Raw JSON prettified --}}
        @php
          $pretty = $record->extra
            ? json_encode(json_decode($record->extra, true), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
            : '';
        @endphp
        @if($pretty)
          <div class="mt-3">
            <div class="text-xs text-slate-500 mb-1">Raw</div>
            <pre class="text-xs bg-slate-50 p-3 rounded overflow-x-auto"><code>{{ $pretty }}</code></pre>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
