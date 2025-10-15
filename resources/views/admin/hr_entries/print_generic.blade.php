{{-- resources/views/admin/hr_entries/print_generic.blade.php --}}
@php
  /** @var \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator $entries */
  $list = $entries instanceof \Illuminate\Pagination\LengthAwarePaginator ? collect($entries->items()) : collect($entries ?? []);
  $site   = $activeSite ?? (isset($sites) ? collect($sites)->firstWhere('id', ($activeSiteId ?? session('site_id'))) : null);
  $siteText = $site ? ($site->code ? ($site->code.' — '.$site->name) : $site->name) : '—';

  $typesMap = collect($types ?? [])->mapWithKeys(fn($t) => [strtolower($t['key'] ?? $t) => ($t['name'] ?? Str::headline($t['key'] ?? $t))])->all();

  $groupBy = strtolower(request('group_by', 'date')); // 'date' | 'type' | 'department'
  $grouped = match ($groupBy) {
      'type'       => $list->groupBy(fn($e) => strtolower($e->type ?? 'other')),
      'department' => $list->groupBy(fn($e) => $e->department ?? '—'),
      default      => $list->groupBy(fn($e) => optional($e->date)->format('Y-m-d') ?? '—'),
  };

  $fmt = fn($v) => is_numeric($v) ? number_format($v, 2) : $v;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Print — HR Daily Entries</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root {
      --ink: #0f172a;
      --muted: #475569;
      --line: #e2e8f0;
      --amber: #f59e0b;
      --emerald: #059669;
      --sky: #0284c7;
    }
    * { box-sizing: border-box; }
    html,body { margin:0; padding:0; color:var(--ink); font: 12px/1.45 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"; }
    @page { size: A4 landscape; margin: 14mm; }
    @media print { .no-print { display:none !important; } .page-break { page-break-before: always; } }
    .toolbar { padding:10px 16px; border-bottom:1px solid var(--line); background:#fff; position: sticky; top:0; }
    .btn { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:10px; border:1px solid var(--line); cursor:pointer; background:#fff; }
    .btn.primary { background: var(--emerald); border-color: var(--emerald); color:#fff; }
    .wrap { padding:18px 22px; }
    .header { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; border-bottom:2px solid var(--ink); padding-bottom:10px; margin-bottom:16px; }
    .brand { display:flex; align-items:center; gap:12px; }
    .brand .logo { width:36px; height:36px; border-radius:12px; background: radial-gradient(120% 120% at -10% -20%, rgba(255,255,255,.9), transparent 60%), linear-gradient(135deg, #10b98122, #38bdf822); border:1px solid #cbd5e1; }
    .brand .title { font-weight:800; font-size:18px; letter-spacing:0.2px; }
    .chips { display:flex; gap:8px; flex-wrap:wrap; }
    .chip { display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:9999px; border:1px solid #e2e8f0; background:#f8fafc; font-size:11px; }
    .chip .dot { width:8px; height:8px; border-radius:2px; }
    .chip.amber .dot { background:var(--amber); }
    .chip.emerald .dot { background:var(--emerald); }
    .chip.sky .dot { background:var(--sky); }
    .meta { display:flex; gap:14px; flex-wrap:wrap; color:var(--muted); font-size:11px; margin-top:6px;}
    h2.group { font-size:14px; margin:18px 0 6px; font-weight:800; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:8px 10px; vertical-align:top; border-bottom:1px solid var(--line); }
    thead th { text-align:left; font-size:11px; color:#0b1220; border-bottom:2px solid var(--ink); }
    tbody tr:nth-child(even) td { background:#fafafa; }
    .t-right { text-align:right; }
    .kv { color:var(--muted); font-size:11px; }
    .foot { display:flex; justify-content:space-between; gap:12px; margin-top:16px; color:var(--muted); font-size:11px; }
  </style>
</head>
<body>

  {{-- TOOLBAR (non-print) --}}
  <div class="toolbar no-print">
    <button class="btn primary" onclick="window.print()">
      🖨️ Print
    </button>
    <button class="btn" onclick="history.back()">
      ← Back
    </button>
  </div>

  <div class="wrap">
    {{-- HEADER --}}
    <div class="header">
      <div class="brand">
        <div class="logo"></div>
        <div>
          <div class="title">HR Daily Entries — Print</div>
          <div class="meta">
            <div><strong>Site:</strong> {{ $siteText }}</div>
            <div><strong>Generated:</strong> {{ now()->format('Y-m-d H:i') }}</div>
            @if(request()->query())
              <div><strong>Filters:</strong> {{ urldecode(http_build_query(request()->query())) }}</div>
            @endif
          </div>
        </div>
      </div>
      <div class="chips">
        <span class="chip amber"><span class="dot"></span> Leave/Permit/Sick</span>
        <span class="chip emerald"><span class="dot"></span> Approved/Processed</span>
        <span class="chip sky"><span class="dot"></span> Pending/Submitted</span>
      </div>
    </div>

    {{-- RINGKASAN --}}
    @php
      $total = $list->count();
      $byType = $list->groupBy(fn($e) => strtolower($e->type ?? 'other'))->map->count();
      $byStatus = $list->groupBy(fn($e) => strtolower($e->status ?? 'unknown'))->map->count();
    @endphp
    <div class="meta" style="margin-top:0;">
      <div><strong>Total:</strong> {{ $total }}</div>
      @foreach($byType as $t => $n)
        <div><strong>{{ $typesMap[$t] ?? Str::headline($t) }}:</strong> {{ $n }}</div>
      @endforeach
      @foreach($byStatus as $s => $n)
        <div><strong>Status {{ Str::upper($s) }}:</strong> {{ $n }}</div>
      @endforeach
    </div>

    {{-- TABEL PER GRUP --}}
    @php $first = true; @endphp
    @foreach($grouped as $gkey => $rows)
      <div class="{{ $first ? '' : 'page-break' }}"></div>
      @php $first = false; @endphp

      <h2 class="group">
        @switch($groupBy)
          @case('type')       Tipe: {{ $typesMap[$gkey] ?? Str::headline($gkey) }} @break
          @case('department') Departemen: {{ $gkey }} @break
          @default            Tanggal: {{ $gkey }} @break
        @endswitch
        — {{ $rows->count() }} entri
      </h2>

      <table>
        <thead>
          <tr>
            <th style="width: 110px;">Date</th>
            <th style="width: 90px;">Type</th>
            <th>User</th>
            <th style="width: 140px;">Department</th>
            <th style="width: 80px;">Shift</th>
            <th>Status</th>
            <th>Note</th>
            <th style="width: 240px;">Meta</th>
            <th style="width: 140px;">Approval</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $e)
            @php
              $meta = is_array($e->meta ?? null) ? $e->meta : [];
              $metaStr = collect($meta)->take(8)->map(function($v,$k) use ($fmt){
                  $vv = is_scalar($v) ? $v : (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : strval($v));
                  return $k.': '.$fmt($vv);
              })->implode(' • ');
            @endphp
            <tr>
              <td>{{ optional($e->date)->format('Y-m-d') ?? '—' }}</td>
              <td>{{ $typesMap[strtolower($e->type ?? '')] ?? Str::headline($e->type ?? '—') }}</td>
              <td>
                {{ $e->user_name ?? ($e->user->name ?? '—') }}
                @if(!empty($e->user_id)) <div class="kv">ID: {{ $e->user_id }}</div> @endif
              </td>
              <td>{{ $e->department ?? '—' }}</td>
              <td class="t-center">{{ $e->shift_slot ?? '—' }}</td>
              <td>
                {{ Str::upper($e->status ?? '—') }}
                @if(!empty($e->submitted_at)) <div class="kv">Submitted: {{ \Illuminate\Support\Carbon::parse($e->submitted_at)->format('Y-m-d H:i') }}</div> @endif
              </td>
              <td>{{ $e->note ?? '—' }}</td>
              <td>{{ $metaStr ?: '—' }}</td>
              <td>
                <div>
                  <div class="kv">By: {{ $e->approved_by_name ?? '—' }}</div>
                  <div class="kv">At: {{ !empty($e->approved_at) ? \Illuminate\Support\Carbon::parse($e->approved_at)->format('Y-m-d H:i') : '—' }}</div>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="9">Tidak ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    @endforeach

    <div class="foot">
      <div>Printed by: {{ auth()->user()->name ?? '—' }}</div>
      <div>Page <span class="page-num"></span></div>
    </div>
  </div>

  <script>
    // Simple page number (works on screen; for PDF engine, rely on header/footer)
    (function(){
      const els = document.querySelectorAll('.page-num');
      els.forEach(el => el.textContent = '1'); // placeholder; browser print usually overrides
      setTimeout(() => window.print(), 50); // auto invoke print on open
    })();
  </script>
</body>
</html>
