<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Cetak · Data Payroal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Print-friendly minimal CSS (tanpa Tailwind agar aman saat dicetak) --}}
  <style>
    :root {
      --fg:#111827; --muted:#6b7280; --line:#e5e7eb; --green:#047857; --red:#b91c1c;
    }
    * { box-sizing: border-box; }
    html,body { margin:0; padding:0; color:var(--fg); font:14px/1.5 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"; }
    .container { max-width: 1024px; margin: 24px auto; padding: 0 16px; }
    .h1 { font-size: 20px; font-weight: 800; margin: 0 0 4px; }
    .muted { color: var(--muted); font-size: 12px; }
    .badges { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px }
    .badge { display:inline-flex; align-items:center; gap:6px; border:1px solid var(--line); border-radius:9999px; padding:2px 8px; font-size:11px; }
    .badge--ok { border-color:#86efac; color:#065f46; background:#ecfdf5; }
    .badge--err{ border-color:#fecaca; color:#991b1b; background:#fef2f2; }

    .toolbar { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:16px; }
    .table { width:100%; border-collapse:collapse; }
    .table th, .table td { border:1px solid var(--line); padding:8px 10px; vertical-align:top; }
    .table th { text-align:left; background:#f8fafc; color:#475569; font-weight:600; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 12px; }
    .right { text-align:right; }
    @media print {
      .no-print { display:none !important; }
      .container { max-width: none; margin:0; padding:0 8mm; }
      .h1 { font-size: 18px; }
    }
  </style>
</head>
<body>
<div class="container">
  <div class="toolbar">
    <div>
      <div class="h1">Data Payroal</div>
      <div class="muted">
        Dicetak: {{ now()->format('d M Y H:i') }}
        @if(!empty($site)) · Site: {{ $site->code ?? '—' }} — {{ $site->name ?? '' }} @endif
      </div>
      <div class="badges">
        <span class="badge">Total: <strong>{{ $total }}</strong></span>
        <span class="badge badge--err">Locked: <strong>{{ $lockedCount }}</strong></span>
        <span class="badge badge--ok">Unlocked: <strong>{{ $unlockedCount }}</strong></span>
      </div>
    </div>

    <button class="no-print" onclick="window.print()" style="padding:8px 12px;border:1px solid var(--line);border-radius:10px;background:#111827;color:#fff;cursor:pointer">
      Cetak
    </button>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th style="width:38mm">User</th>
        <th style="width:52mm">Email</th>
        <th style="width:30mm">Emp. Code</th>
        <th style="width:32mm">NIK</th>
        <th style="width:50mm">Site</th>
        <th style="width:28mm">Status</th>
        <th style="width:26mm">Join</th>
        <th style="width:18mm" class="right">Lock</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($rows as $r)
        @php
          $u  = $r->user;
          $sc = $r->site->code ?? null;
          $sn = $r->site->name ?? null;
          $status = strtoupper($r->employment_status ?? '—');
        @endphp
        <tr>
          <td>{{ $u->name ?? '—' }}</td>
          <td>{{ $u->email ?? '—' }}</td>
          <td class="mono">{{ $r->employee_code ?? '—' }}</td>
          <td class="mono">{{ $r->nik ?? '—' }}</td>
          <td>
            @if($sc || $sn)
              <strong>{{ $sc ?: '—' }}</strong>@if($sn) — <span class="muted">{{ $sn }}</span>@endif
            @else — @endif
          </td>
          <td>{{ $status }}</td>
          <td>{{ $r->hire_date ? \Illuminate\Support\Carbon::parse($r->hire_date)->format('d M Y') : '—' }}</td>
          <td class="right">
            @if($r->self_locked) 🔒 @else 🔓 @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:18px">Tidak ada data.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:10px" class="muted">
    Catatan: Tampilan ini dioptimalkan untuk kertas A4 potret.
  </div>
</div>
</body>
</html>
