{{-- resources/views/admin/users/index.blade.php (UI diseragamkan hijau–emas–biru) --}}
@extends('layouts.app')
@section('title','Kelola User')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

  {{-- HERO / PAGE TITLE --}}

  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>
    <div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-start gap-3">
        <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">👥 Daftar User</h1>
          <p class="text-white/90 text-sm">Kelola role, divisi, dan akses.</p>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        @isset($users)
        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/25">
          <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
          Total: {{ method_exists($users,'total') ? $users->total() : (is_countable($users)?count($users):'-') }}
        </span>
        @endisset
        @if(Route::has('admin.users.create'))
        <a href="{{ route('admin.users.create') }}"
          class="px-3 py-2 rounded-xl text-sm font-semibold text-slate-900 bg-amber-300 hover:bg-amber-200 ring-1 ring-amber-400/50 transition">
          + Tambah User
        </a>
        @endif
      </div>
    </div>

  </div>

  {{-- FLASH / ERRORS --}}
  @if (session('success'))
  <div class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 text-sm">
    {{ session('success') }}
  </div>
  @endif
  @if ($errors->any())
  <div class="px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
    <ul class="list-disc pl-5 space-y-0.5">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
  @endif

  {{-- FILTER BAR --}}

  <div class="px-6 sm:px-8 py-5 bg-white rounded-3xl shadow ring-1 ring-slate-200">
    <form method="get" class="grid gap-3 sm:grid-cols-[1fr_auto]">
      <div class="relative"> <input type="text" name="q" value="{{ $search ?? request('q') }}" class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-24 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600" placeholder="Cari nama / email…"> <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
        </svg> <button class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-sky-700 text-white ring-1 ring-white/30 hover:bg-sky-600 transition"> Cari </button> </div> @if(request()->filled('q')) <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50"> Reset </a> @endif
    </form>
  </div>

  {{-- TABLE --}}

  <div class="rounded-3xl shadow ring-1 ring-emerald-900/10 overflow-hidden bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="sticky top-0 bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-emerald-900/10 z-10">
          <tr>
            <th class="px-4 py-3 text-left font-semibold">Nama</th>
            <th class="px-4 py-3 text-left font-semibold">Email</th>
            <th class="px-4 py-3 text-left font-semibold">Role</th>
            <th class="px-4 py-3 text-left font-semibold">Division</th>
            <th class="px-4 py-3 text-center font-semibold w-44">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 [&>tr:hover]:bg-emerald-50/60"> @forelse($users as $u) <tr>
            <td class="px-4 py-3 font-medium text-slate-900"> {{ $u->name }} </td>
            <td class="px-4 py-3 font-mono text-emerald-700"> {{ $u->email }} </td>
            <td class="px-4 py-3"> @if($u->role?->name) <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200"> {{ $u->role->name }} </span> @else <span class="text-slate-400">—</span> @endif </td>
            <td class="px-4 py-3">{{ $u->division?->name ?? '—' }}</td>
            <td class="px-4 py-3 text-center">
              <div class="inline-flex gap-2"> @if(Route::has('admin.access.users.role.edit')) <a href="{{ route('admin.access.users.role.edit',$u) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-teal-600 text-white shadow ring-1 ring-teal-700/20 hover:bg-teal-700 transition"> <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6h.01M12 12h.01M12 18h.01" />
                  </svg> Ubah Akses </a> @endif @if(Route::has('admin.users.edit')) <a href="{{ route('admin.users.edit',$u) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-sky-600 text-white shadow ring-1 ring-sky-700/20 hover:bg-sky-700 transition"> <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z" />
                  </svg> Edit </a> @endif </div>
            </td>
          </tr> @empty <tr>
            <td colspan="5" class="px-4 py-10">
              <div class="text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center"> <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" />
                  </svg> </div>
                <p class="mt-3 text-slate-700 font-medium">Belum ada user</p> @if(Route::has('admin.users.create')) <a href="{{ route('admin.users.create') }}" class="inline-flex mt-2 items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20 shadow"> + Tambah sekarang </a> @endif
              </div>
            </td>
          </tr> @endforelse </tbody>
      </table>
    </div>
    {{-- Pagination --}}
    <div class="px-4 py-4 border-t bg-slate-50">
      {{ $users->withQueryString()->onEachSide(1)->links() }}
    </div>

  </div>
</div> 
@endsection