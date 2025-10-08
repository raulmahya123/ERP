{{-- resources/views/admin/access/users/role.blade.php --}}
@extends('layouts.app')
@section('title','Ubah Akses User')

@section('content')
<style>[x-cloak]{display:none}</style>

{{-- ===== HERO (serumpun hijau–emas–biru) ===== --}}
<div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10 mb-6 max-w-3xl mx-auto">
  <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
  <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

  <div class="relative px-6 sm:px-8 py-5 text-white flex items-center justify-between">
    <div class="flex items-start gap-3">
      <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
        </svg>
      </div>
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
          Ubah Akses User
        </h1>
        <p class="text-white/90 text-sm">Atur role &amp; divisi user dengan gaya seragam hijau–emas–biru.</p>
      </div>
    </div>

    <a href="{{ route('admin.access.users.index') }}"
       class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 hover:bg-white/15 transition">
      ← Kembali
    </a>
  </div>
</div>

{{-- ===== CARD ===== --}}
<div class="max-w-3xl mx-auto">
  {{-- FLASH / ERRORS --}}
  @if (session('status') || session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('status') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-700 ring-1 ring-red-200 text-sm">
      <div class="font-semibold mb-1">Gagal menyimpan:</div>
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
      <h2 class="font-semibold text-slate-800">Detail User</h2>
    </div>

    <div class="p-6">
      <form method="post" action="{{ route('admin.access.users.role',$user) }}" class="grid gap-5">
        @csrf

        {{-- Nama & Email --}}
        <div>
          <div class="text-xs text-slate-500 mb-1">Nama</div>
          <div class="font-medium text-slate-800">
            {{ $user->name }}
            <span class="text-slate-500">({{ $user->email }})</span>
          </div>
        </div>

        {{-- Role --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Role <span class="text-red-600">*</span></label>
          <select name="role_id" required
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                         focus:border-emerald-600 focus:ring-emerald-600">
            @foreach($roles as $r)
              <option value="{{ $r->id }}" @selected($user->role_id===$r->id)>{{ $r->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Division --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Division (opsional)</label>
          <select name="division_id"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                         focus:border-emerald-600 focus:ring-emerald-600">
            <option value="">—</option>
            @foreach($divisions as $d)
              <option value="{{ $d->id }}" @selected($user->division_id===$d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 pt-1">
          <button
            class="px-4 py-2 rounded-xl font-semibold text-white
                   bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700
                   hover:from-emerald-700 hover:to-sky-800 shadow">
            Simpan
          </button>
          <a href="{{ route('admin.access.users.index') }}"
             class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
