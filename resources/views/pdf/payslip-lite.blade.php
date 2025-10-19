<!doctype html>
<html><head><meta charset="utf-8"><title>Payslip</title>
<style>body{font-family:ui-sans-serif;}</style></head>
<body>
  <h2>Payslip {{ $h->period->format('F Y') }}</h2>
  <p>Nama: {{ $h->user?->name }} | NIK: {{ $h->payroal?->nik }}</p>
  <hr>
  <h4>Pendapatan</h4>
  <ul>
    @foreach(($h->earnings ?? []) as $e)
      <li>{{ $e['name'] ?? '-' }}: {{ number_format((float)($e['amount']??0),2,',','.') }}</li>
    @endforeach
  </ul>
  <h4>Potongan</h4>
  <ul>
    @foreach(($h->deductions ?? []) as $d)
      <li>{{ $d['name'] ?? '-' }}: {{ number_format((float)($d['amount']??0),2,',','.') }}</li>
    @endforeach
  </ul>
  <p><strong>THP: {{ number_format($h->take_home_pay,2,',','.') }}</strong></p>
</body></html>
