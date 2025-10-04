{{-- resources/views/admin/master/index.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('title','Master: ' . Str::headline($entity))

@section('header')
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <span class="inline-flex items-center rounded-full bg-[#0d2b52]/10 text-[#0d2b52] px-3 py-1 text-xs font-semibold">GM</span>
      <h2 class="font-semibold text-xl text-slate-800">
        Master — {{ Str::headline($entity) }}
      </h2>
    </div>

    <div class="flex items-center gap-2">
      @if (Route::has('admin.master.overview'))
        <a href="{{ route('admin.master.overview') }}"
           class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">
          Overview
        </a>
      @endif
      <a href="{{ route('admin.master.create', $entity) }}"
         class="px-3 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700">
        + Create
      </a>
    </div>
  </div>
@endsection

@section('content')
  {{-- Flash status --}}
  @if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
      {{ session('status') }}
    </div>
  @endif

  {{-- Toolbar: search + util --}}
  <div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
    <div class="px-4 py-3 border-b bg-slate-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <form method="GET" class="flex-1">
        <div class="flex gap-2">
          <input type="text" name="q" value="{{ $search }}" placeholder="Cari name/code/description..."
                 class="border rounded-md px-3 py-2 w-full">
          <button class="px-3 py-2 rounded-md bg-slate-900 text-white text-sm hover:bg-slate-800">Search</button>
        </div>
      </form>

      <div class="flex items-center gap-2">
        @if (Route::has('admin.master.import.template'))
          <a href="{{ route('admin.master.import.template', $entity) }}"
             class="px-3 py-2 rounded-md text-sm bg-white border hover:bg-slate-50">Template</a>
        @endif
        @if (Route::has('admin.master.export'))
          <a href="{{ route('admin.master.export', $entity) }}"
             class="px-3 py-2 rounded-md text-sm bg-white border hover:bg-slate-50">Export CSV</a>
        @endif
        @if (Route::has('admin.master.import'))
          <form method="POST" action="{{ route('admin.master.import', $entity) }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <label class="text-sm px-3 py-2 rounded-md bg-white border hover:bg-slate-50 cursor-pointer">
              <input type="file" name="file" accept=".csv" class="hidden" onchange="this.form.submit()">
              Import CSV
            </label>
          </form>
        @endif
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-50 text-left">
            <th class="px-3 py-3 w-56">Name</th>
            <th class="px-3 py-3 w-40">Code</th>
            <th class="px-3 py-3">Description</th>
            <th class="px-3 py-3 w-40">Updated</th>
            <th class="px-3 py-3 w-40">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($records as $r)
            <tr class="border-t">
              {{-- Name → Show --}}
              <td class="px-3 py-2 font-medium">
                <a href="{{ route('admin.master.show', ['entity'=>$entity,'record'=>$r->id]) }}"
                   class="text-slate-800 hover:underline">
                  {{ $r->name }}
                </a>
              </td>
              <td class="px-3 py-2 text-slate-600">{{ $r->code ?? '—' }}</td>
              <td class="px-3 py-2 text-slate-600">
                <span title="{{ $r->description }}">{{ Str::limit((string) $r->description, 120) ?: '—' }}</span>
              </td>
              <td class="px-3 py-2 text-slate-500">
                {{ $r->updated_at }}
              </td>
              <td class="px-3 py-2">
                <div class="flex items-center gap-3">
                  <a href="{{ route('admin.master.show', ['entity'=>$entity,'record'=>$r->id]) }}" class="text-slate-700">Show</a>
                  <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$r->id]) }}" class="text-blue-600">Edit</a>
                  <a href="{{ route('admin.master.permissions', ['entity'=>$entity,'record'=>$r->id]) }}" class="text-amber-600">Perms</a>
                  <form method="POST" action="{{ route('admin.master.destroy', ['entity'=>$entity,'record'=>$r->id]) }}"
                        onsubmit="return confirm('Hapus data ini?')" class="inline">
                    @csrf @method('DELETE')
                    <button class="text-red-600">Del</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-3 py-10 text-center text-slate-500">
                Belum ada data. <a class="text-emerald-700 underline" href="{{ route('admin.master.create',$entity) }}">Buat sekarang</a>.
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
