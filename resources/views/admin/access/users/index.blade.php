{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')
@section('title','Kelola User')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-emerald-900/10 overflow-hidden">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">👥 Daftar User</h1>
          <p class="text-white/90 text-sm mt-1">Kelola role, divisi, dan akses.</p>
        </div>
        @isset($users)
        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
          <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
          Total: {{ method_exists($users,'total') ? $users->total() : (is_countable($users)?count($users):'-') }}
        </span>
        @endisset
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-emerald-900/5">
    <form method="get" class="grid gap-3 sm:grid-cols-[1fr_auto]">
      <div class="relative">
        <input type="text" name="q" value="{{ $search ?? request('q') }}"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-24 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600"
               placeholder="Cari nama / email…">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
        </svg>
        <button class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-sky-700 text-white ring-1 ring-white/30 hover:bg-sky-600 transition">
          Cari
        </button>
      </div>
      @if(request()->filled('q'))
      <a href="{{ route('admin.users.index') }}"
         class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">
        Reset
      </a>
      @endif
    </form>
  </div>

  {{-- TABLE --}}
  <div class="p-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-emerald-900/10 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-emerald-900/10">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Nama</th>
              <th class="px-4 py-3 text-left font-semibold">Email</th>
              <th class="px-4 py-3 text-left font-semibold">Role</th>
              <th class="px-4 py-3 text-left font-semibold">Division</th>
              <th class="px-4 py-3 text-center font-semibold w-40">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($users as $u)
            <tr class="hover:bg-emerald-50/60">
              <td class="px-4 py-3 font-medium text-slate-900">{{ $u->name }}</td>
              <td class="px-4 py-3 font-mono text-emerald-700">{{ $u->email }}</td>
              <td class="px-4 py-3">
                @if($u->role?->name)
                  <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    {{ $u->role->name }}
                  </span>
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>
              <td class="px-4 py-3">{{ $u->division?->name ?? '—' }}</td>
              <td class="px-4 py-3">
                <a href="{{ route('admin.access.users.role.edit',$u) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                          bg-teal-600 text-white shadow ring-1 ring-teal-700/20 hover:bg-teal-700 transition">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6h.01M12 12h.01M12 18h.01"/>
                  </svg>
                  Ubah Akses
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="px-4 py-4 border-t bg-slate-50">
        {{ $users->withQueryString()->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
