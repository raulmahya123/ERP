{{-- resources/views/admin/payroal/index.blade.php (UI diseragamkan dgn hirau-emas-biru) --}}
@extends('layouts.app')

@section('content')
@php
// ringkasan di halaman (berbasis data halaman saat ini)
$pageLocked = $items->getCollection()->filter(fn($r) => (bool)($r->self_locked ?? false))->count();
$pageUnlocked = $items->count() - $pageLocked;
@endphp

<style>
    [x-cloak] {
        display: none
    }
</style>
<div class="max-w-7xl mx-auto space-y-6">

    {{-- HERO / PAGE TITLE (serumpun hijau-emas-biru) --}}

    <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
        <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
        <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>
        <div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="h-11 w-11 rounded-2xl bg-white/10 text-white grid place-items-center ring-1 ring-white/20 shadow-sm">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Data Payroal</h1>

                    {{-- Badges summary --}}
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-white/90 text-xs">
                        @if(!empty($site) && !empty($site->id))
                        <span class="px-2 py-0.5 rounded-full bg-white/10 ring-1 ring-white/25">
                            Site: <strong>{{ $site->code ?? '—' }}</strong> — {{ $site->name ?? '' }}
                        </span>
                        @endif
                        <span class="px-2 py-0.5 rounded-full bg-white/10 ring-1 ring-white/25">
                            Total: <strong>{{ $items->total() }}</strong>
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-rose-500/20 ring-1 ring-rose-300/50">
                            Locked: <strong>{{ $pageLocked }}</strong>
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 ring-1 ring-emerald-300/60">
                            Unlocked: <strong>{{ $pageUnlocked }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.payroal.print', request()->only('q','site_id')) }}"
                    class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 text-white hover:bg-white/15 transition inline-flex items-center gap-2"
                    target="_blank" rel="noopener">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 14h12v8H6z" />
                        <path d="M18 8H6" />
                    </svg>
                    Print
                </a>

                <a href="{{ route('admin.payroal.export.csv', request()->only('q','site_id')) }}"
                    class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 text-white hover:bg-white/15 transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 3v12" />
                        <path d="m7 10 5 5 5-5" />
                        <path d="M5 21h14" />
                    </svg>
                    Export CSV
                </a>

                <a href="{{ route('admin.payroal.create') }}"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-900 bg-amber-300 hover:bg-amber-200 ring-1 ring-amber-400/50 transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    Tambah
                </a>
            </div>
        </div>

    </div>

    {{-- FLASH --}}
    {{-- FILTER BAR (seragam) --}}

    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
            <div class="text-sm font-semibold text-slate-800">Filter</div>
        </div>
        <div class="p-5">
            <form method="GET" class="grid md:grid-cols-12 gap-3 md:items-end">
                <div class="md:col-span-6"> <label class="block text-xs font-medium text-slate-600 mb-1">Pencarian</label> <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari: nama / email / employee_code / NIK" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-emerald-300 focus:outline-none"> </div>
                <div class="md:col-span-3"> <label class="block text-xs font-medium text-slate-600 mb-1">Site</label> <select name="site_id" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                        <option value="">Semua</option> @isset($sites) @foreach($sites as $opt) <option value="{{ $opt->id }}" @selected(($site_id ?? '' )===$opt->id)>{{ $opt->code }} — {{ $opt->name }}</option> @endforeach @endisset
                    </select> </div>
                <div class="md:col-span-3">
                    <div class="flex gap-2"> <button class="px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Cari</button> <a href="{{ route('admin.payroal.index') }}" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50">Reset</a> </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE --}}

    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 w-48">User</th>
                    <th class="text-left px-4 py-3 w-56">Email</th>
                    <th class="text-left px-4 py-3 w-56">Emp. Code</th>
                    <th class="text-left px-4 py-3 w-44">NIK</th>
                    <th class="text-left px-4 py-3 w-64">Site</th>
                    <th class="text-left px-4 py-3 w-32">Status</th>
                    <th class="text-left px-4 py-3 w-28">Join</th>
                    <th class="text-right px-4 py-3 w-56">Aksi</th>
                </tr>
            </thead>
            <tbody class="[&>tr:nth-child(even)]:bg-slate-50/40"> @forelse ($items as $row) @php $u = $row->user; $sc = $row->site->code ?? null; $sn = $row->site->name ?? null;
                $lbl = $row->employment_status ?? '—';
                $cls = match($row->employment_status) {
                'permanent' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                'contract' => 'bg-amber-100 text-amber-700 ring-amber-200',
                'probation' => 'bg-sky-100 text-sky-700 ring-sky-200',
                'intern' => 'bg-violet-100 text-violet-700 ring-violet-200',
                default => 'bg-slate-100 text-slate-600 ring-slate-200'
                };
                $isLocked = (bool)($row->self_locked ?? false);
                @endphp

                <tr class="align-middle {{ $isLocked ? 'bg-slate-50' : '' }} border-t">
                    <td class="px-4 py-3 text-slate-800 font-medium">
                        <div class="flex items-center gap-2">
                            {{-- lock bubble --}}
                            @if($isLocked)
                            <span title="Self-service Terkunci"
                                class="inline-flex items-center justify-center w-6 h-6 rounded-full ring-1 ring-rose-200 bg-rose-50 text-rose-600">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <rect x="4" y="11" width="16" height="9" rx="2"></rect>
                                    <path d="M8 11V8a4 4 0 0 1 8 0v3"></path>
                                </svg>
                            </span>
                            @else
                            <span title="Self-service Belum Terkunci"
                                class="inline-flex items-center justify-center w-6 h-6 rounded-full ring-1 ring-emerald-200 bg-emerald-50 text-emerald-600">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <rect x="4" y="11" width="16" height="9" rx="2"></rect>
                                    <path d="M8 11V8a4 4 0 0 1 7.5-2"></path>
                                </svg>
                            </span>
                            @endif

                            <span class="whitespace-nowrap">{{ $u->name ?? '—' }}</span>
                        </div>

                        <div class="mt-1">
                            @if($isLocked)
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 ring-1 ring-rose-200">Locked</span>
                            @else
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Unlocked</span>
                            @endif
                        </div>
                    </td>

                    <td class="px-4 py-3 text-slate-600">
                        <div class="truncate max-w-[220px]">{{ $u->email ?? '—' }}</div>
                    </td>

                    <td class="px-4 py-3">
                        <span class="font-mono text-xs whitespace-nowrap">{{ $row->employee_code ?? '—' }}</span>
                    </td>

                    <td class="px-4 py-3">
                        <span class="font-mono text-xs whitespace-nowrap">{{ $row->nik ?? '—' }}</span>
                    </td>

                    <td class="px-4 py-3">
                        @if($sc || $sn)
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 whitespace-nowrap">
                            <strong class="font-semibold">{{ $sc ?: '—' }}</strong>
                            @if($sn) <span class="opacity-60">— {{ $sn }}</span> @endif
                        </span>
                        @else
                        —
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full ring-1 {{ $cls }} whitespace-nowrap">{{ strtoupper($lbl) }}</span>
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $row->hire_date ? \Illuminate\Support\Carbon::parse($row->hire_date)->format('d M Y') : '—' }}
                    </td>

                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-3">
                            <a href="{{ route('admin.payroal.edit', $row) }}" class="text-emerald-700 hover:underline">Edit</a>

                            @if(!$isLocked)
                            <form action="{{ route('admin.payroal.lock', $row) }}" method="POST" class="inline"
                                onsubmit="return confirm('Kunci data ini? Pengguna tidak bisa edit sendiri setelah dikunci.')">
                                @csrf
                                <button class="text-slate-700 hover:underline">Kunci</button>
                            </form>
                            @else
                            <form action="{{ route('admin.payroal.unlock', $row) }}" method="POST" class="inline"
                                onsubmit="return confirm('Buka kunci data ini? Pengguna bisa edit lagi.')">
                                @csrf
                                <button class="text-amber-700 hover:underline">Buka</button>
                            </form>
                            @endif

                            <form action="{{ route('admin.payroal.destroy', $row) }}" method="POST" class="inline"
                                onsubmit="return confirm('Hapus data payroal ini?')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12">
                        <div class="text-center">
                            <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center">
                                <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" />
                                </svg>
                            </div>
                            <p class="mt-3 text-slate-700 font-medium">Belum ada data</p>
                            <a href="{{ route('admin.payroal.create') }}"
                                class="inline-flex mt-2 items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold
                      bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20 shadow">
                                + Buat sekarang
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    {{-- legend kecil --}}

    <div class="mt-3 text-[11px] text-slate-500 flex items-center gap-3"> <span class="inline-flex items-center gap-1"> <span class="inline-flex w-4 h-4 rounded-full ring-1 ring-rose-200 bg-rose-50"></span> Locked </span> <span class="inline-flex items-center gap-1"> <span class="inline-flex w-4 h-4 rounded-full ring-1 ring-emerald-200 bg-emerald-50"></span> Unlocked </span> </div>

    {{-- PAGINATION (seragam) --}}

    <div> {{ $items->withQueryString()->links() }} </div>
</div> 
@endsection