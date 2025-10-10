{{-- resources/views/admin/manpower/dashboard.blade.php --}}
@extends('layouts.app')
@section('title','Manpower — Dashboard')

@section('content')
<div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  {{-- Header --}}
  <div class="relative px-6 py-6 text-white">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-700 to-sky-700"></div>
    <div class="relative flex flex-col md:flex-row md:items-end md:justify-between gap-4">
      <div class="space-y-1">
        <h1 class="text-2xl font-bold tracking-tight">👷 Manpower Dashboard</h1>
        <p class="text-white/90 text-sm">Plan vs Realisasi, crew utilization & produktivitas.</p>
      </div>
      <form method="GET" class="relative z-10 flex flex-wrap items-center gap-2">
        <input type="date" name="date" value="{{ $date }}" class="border border-white/30 bg-white/10 text-white placeholder-white/70 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/60">
        <select name="shift_slot" class="border border-white/30 bg-white/10 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/60">
          @foreach(['A','B','C','D','NON'] as $opt)
            <option class="text-slate-900" value="{{ $opt }}" @selected($shift===$opt)>{{ $opt }}</option>
          @endforeach
        </select>
        <button class="px-4 py-2 rounded-lg bg-white/10 ring-1 ring-white/50 hover:bg-white/20 font-semibold">Terapkan</button>
      </form>
    </div>
  </div>

  <div class="p-6 space-y-6">
    @if (session('success'))
      <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
        {{ session('success') }}
      </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="p-4 rounded-xl border hover:shadow-sm">
        <div class="text-xs text-slate-500">Headcount (Plan)</div>
        <div class="text-2xl font-bold">{{ (int)($kpi['headcount_plan'] ?? 0) }}</div>
      </div>
      <div class="p-4 rounded-xl border hover:shadow-sm">
        <div class="text-xs text-slate-500">Headcount (Actual)</div>
        <div class="text-2xl font-bold">{{ (int)($kpi['headcount_actual'] ?? 0) }}</div>
      </div>
      <div class="p-4 rounded-xl border hover:shadow-sm">
        <div class="text-xs text-slate-500">Crew Fill Rate</div>
        <div class="text-2xl font-bold">{{ number_format((float)($kpi['crew_fill_rate'] ?? 0),1) }}%</div>
      </div>
      <div class="p-4 rounded-xl border hover:shadow-sm">
        <div class="text-xs text-slate-500">Prod / Manhour</div>
        <div class="text-2xl font-bold">{{ number_format((float)($kpi['productivity_per_mh'] ?? 0),2) }}</div>
      </div>
    </div>

    {{-- Form Plan --}}
    <div class="rounded-xl border p-4">
      <h2 class="font-semibold mb-3">📝 Input Plan</h2>
      @if ($errors->any())
        <div class="mb-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-3 py-2">
          <ul class="list-disc list-inside">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif
      <form action="{{ route('admin.manpower.plan.store') }}" method="POST" class="grid md:grid-cols-6 gap-3">
        @csrf
        <input type="hidden" name="site_id" value="{{ session('site_id') }}">
        <input type="date" name="date" value="{{ $date }}" class="border rounded-lg px-3 py-2" required>
        <select name="shift_slot" class="border rounded-lg px-3 py-2" required>
          @foreach(['A','B','C','D','NON'] as $opt)
            <option value="{{ $opt }}" @selected($shift===$opt)>{{ $opt }}</option>
          @endforeach
        </select>
        <input type="text" name="department" placeholder="Departemen" class="border rounded-lg px-3 py-2 md:col-span-2" required>
        <input type="number" name="planned_headcount" min="0" class="border rounded-lg px-3 py-2" placeholder="Planned HC" required>
        <input type="text" name="note" class="border rounded-lg px-3 py-2" placeholder="Catatan (opsional)">
        <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-semibold hover:bg-teal-700">Simpan Plan</button>
      </form>
    </div>

    {{-- Form Realisasi --}}
    <div class="rounded-xl border p-4">
      <h2 class="font-semibold mb-3">✅ Input Realisasi</h2>
      <form action="{{ route('admin.manpower.realization.store') }}" method="POST" class="grid md:grid-cols-6 gap-3">
        @csrf
        <input type="hidden" name="site_id" value="{{ session('site_id') }}">
        <input type="date" name="date" value="{{ $date }}" class="border rounded-lg px-3 py-2" required>
        <select name="shift_slot" class="border rounded-lg px-3 py-2" required>
          @foreach(['A','B','C','D','NON'] as $opt)
            <option value="{{ $opt }}" @selected($shift===$opt)>{{ $opt }}</option>
          @endforeach
        </select>
        <input type="text" name="department" placeholder="Departemen" class="border rounded-lg px-3 py-2 md:col-span-2" required>
        <input type="number" name="actual_headcount" min="0" class="border rounded-lg px-3 py-2" placeholder="Actual HC" required>
        <input type="number" step="0.01" name="manhours" min="0" class="border rounded-lg px-3 py-2" placeholder="Manhours">
        <input type="number" step="0.01" name="production_tonnage" min="0" class="border rounded-lg px-3 py-2" placeholder="Production (t)">
        <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700">Simpan Realisasi</button>
      </form>
    </div>

    {{-- Lists --}}
    <div class="grid md:grid-cols-2 gap-4">
      <div class="rounded-xl border overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 border-b font-semibold">Plan ({{ $date }} — {{ $shift }})</div>
        <div class="p-4 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead><tr class="text-slate-600">
              <th class="text-left px-2 py-2">Departemen</th>
              <th class="text-left px-2 py-2">Planned HC</th>
              <th class="text-left px-2 py-2">Note</th>
            </tr></thead>
            <tbody>
            @forelse($plans as $p)
              <tr class="border-t hover:bg-slate-50/60">
                <td class="px-2 py-2 font-medium">{{ $p->department }}</td>
                <td class="px-2 py-2">{{ (int)$p->planned_headcount }}</td>
                <td class="px-2 py-2">{{ $p->note ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="px-2 py-4 text-center text-slate-500">Belum ada plan.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="rounded-xl border overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 border-b font-semibold">Realisasi ({{ $date }} — {{ $shift }})</div>
        <div class="p-4 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead><tr class="text-slate-600">
              <th class="text-left px-2 py-2">Departemen</th>
              <th class="text-left px-2 py-2">Actual HC</th>
              <th class="text-left px-2 py-2">MH</th>
              <th class="text-left px-2 py-2">Prod (t)</th>
            </tr></thead>
            <tbody>
            @forelse($real as $x)
              <tr class="border-t hover:bg-slate-50/60">
                <td class="px-2 py-2 font-medium">{{ $x->department }}</td>
                <td class="px-2 py-2">{{ (int)$x->actual_headcount }}</td>
                <td class="px-2 py-2">{{ number_format((float)($x->manhours ?? 0),2) }}</td>
                <td class="px-2 py-2">{{ number_format((float)($x->production_tonnage ?? 0),2) }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="px-2 py-4 text-center text-slate-500">Belum ada realisasi.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Assignments --}}
    <div class="rounded-xl border overflow-hidden">
      <div class="px-4 py-3 bg-slate-50 border-b font-semibold">Mapping Crew ({{ $date }} — {{ $shift }})</div>
      <div class="p-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead><tr class="text-slate-600">
            <th class="text-left px-2 py-2">User</th>
            <th class="text-left px-2 py-2">Role</th>
            <th class="text-left px-2 py-2">Equipment</th>
            <th class="text-left px-2 py-2">Activity</th>
            <th class="text-left px-2 py-2">Remarks</th>
          </tr></thead>
          <tbody>
            @forelse($assignments as $a)
              <tr class="border-t hover:bg-slate-50/60">
                <td class="px-2 py-2 font-medium">{{ $a->user->name ?? $a->user_id }}</td>
                <td class="px-2 py-2">{{ $a->role }}</td>
                <td class="px-2 py-2">
                  {{ $a->equipment?->code ? ($a->equipment->code.' — '.$a->equipment->name) : '—' }}
                </td>
                <td class="px-2 py-2">{{ $a->activity_code ?? '—' }}</td>
                <td class="px-2 py-2">{{ $a->remarks ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="px-2 py-4 text-center text-slate-500">Belum ada mapping.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
