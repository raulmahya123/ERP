@php
$u = $h->user;
@endphp
<p>Yth. {{ $u?->name ?? 'Karyawan' }},</p>
<p>Berikut kami sampaikan payslip periode <strong>{{ $h->period->translatedFormat('F Y') }}</strong>.</p>
<ul>
  <li>Gaji Bruto: {{ number_format($h->gross,2,',','.') }}</li>
  <li>Potongan: {{ number_format($h->deduction,2,',','.') }}</li>
  <li>Take Home Pay: <strong>{{ number_format($h->take_home_pay,2,',','.') }}</strong></li>
</ul>
<p>Dokumen terlampir atau akses tautan aman (hanya Anda):</p>
<p><a href="{{ route('my.payslip.view', ['token' => $h->view_token]) }}">Lihat Payslip</a></p>
<p>Terima kasih.</p>
