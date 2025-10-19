@php
  /** @var \App\Models\Payslip $h */
  /** @var \App\Models\User|null $u */
  $u = $h->user ?? null;

  // ==== Logo: pakai CID jika tersedia (lebih kompatibel), kalau tidak pakai URL asset ====
  $logoPath = public_path('assets/logo.png');
  $logoUrl  = asset('assets/logo.png');
  $logoCid  = null;

  // Pada mailable Markdown, variabel $message tersedia & mendukung embed()
  if (isset($message) && is_file($logoPath)) {
      try { $logoCid = $message->embed($logoPath); } catch (\Throwable $e) {}
  }
  $iconSrc = $logoCid ?: $logoUrl;

  // Preheader (teks kecil yang muncul di preview klien email)
  $preheader = "Payslip {$h->period->translatedFormat('F Y')} — THP: " .
               number_format((float)$h->take_home_pay, 2, ',', '.');
@endphp

<!doctype html>
<html lang="id" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="color-scheme" content="light dark">
  <meta name="supported-color-schemes" content="light dark">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payslip {{ $h->period->translatedFormat('F Y') }}</title>
  <style>
    /* Reset ringkas untuk email */
    body,table,td,a { font-family: ui-sans-serif, -apple-system, Segoe UI, Roboto, Helvetica, Arial; }
    img { border:0; outline:none; text-decoration:none; display:block; }
    a { color:#0f766e; text-decoration:none; }
    .btn { background:#059669; color:#fff !important; padding:10px 16px; border-radius:10px; display:inline-block; font-weight:700; }
    .badge { display:inline-flex; align-items:center; gap:8px; background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; border-radius:9999px; padding:6px 10px; font-size:12px; }
    .card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px; }
    @media (prefers-color-scheme: dark) {
      body { background:#0b1020; color:#e5e7eb; }
      .card { background:#111827; border-color:#1f2937; }
      .badge { background:#083f2a; border-color:#065f46; color:#d1fae5; }
      .btn { background:#10b981; }
      a { color:#34d399; }
    }
  </style>
</head>
<body style="margin:0; padding:0; background:#f3f4f6;">

  <!-- Preheader – disembunyikan di body tapi terbaca preview -->
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
    {{ $preheader }}
  </div>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6; padding:24px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;">
          <!-- Header -->
          <tr>
            <td style="padding:8px 8px 16px;" align="left">
              <span class="badge" aria-label="BISA Payslip">
                <img src="{{ $iconSrc }}" width="18" height="18" alt="Logo BISA">
                <strong style="margin-right:4px;">BISA</strong>
                <span style="opacity:.9;">Payslip</span>
              </span>
            </td>
          </tr>

          <!-- Card konten -->
          <tr>
            <td class="card">
              <p style="margin:0 0 12px;">Yth. {{ $u?->name ?? 'Karyawan' }},</p>
              <p style="margin:0 0 12px;">
                Berikut kami sampaikan payslip periode
                <strong>{{ $h->period->translatedFormat('F Y') }}</strong>.
              </p>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:12px 0 16px;">
                <tr>
                  <td style="padding:6px 0; width:40%;">Gaji Bruto</td>
                  <td style="padding:6px 0;"><strong>{{ number_format((float)$h->gross, 2, ',', '.') }}</strong></td>
                </tr>
                <tr>
                  <td style="padding:6px 0;">Potongan</td>
                  <td style="padding:6px 0;"><strong>{{ number_format((float)$h->deduction, 2, ',', '.') }}</strong></td>
                </tr>
                <tr>
                  <td style="padding:6px 0;">Take Home Pay</td>
                  <td style="padding:6px 0;"><strong>{{ number_format((float)$h->take_home_pay, 2, ',', '.') }}</strong></td>
                </tr>
              </table>

              <p style="margin:0 0 10px;">Dokumen terlampir atau akses tautan aman (hanya Anda):</p>

              <!-- Tombol ke route my.payslip.view -->
              <p style="margin:14px 0 18px;">
                <a class="btn" href="{{ route('my.payslip.view', ['token' => $h->view_token]) }}" aria-label="Lihat Payslip periode {{ $h->period->translatedFormat('F Y') }}">
                  <table role="presentation" cellspacing="0" cellpadding="0">
                    <tr>
                      <td style="vertical-align:middle; padding-right:8px;">
                        <img src="{{ $iconSrc }}" width="20" height="20" alt="" style="border-radius:4px;">
                      </td>
                      <td style="vertical-align:middle; color:#fff; font-weight:700;">Lihat Payslip</td>
                    </tr>
                  </table>
                </a>
              </p>

              <!-- fallback link plain -->
              <p style="margin:0 0 4px; font-size:13px; opacity:.8;">
                Jika tombol tidak berfungsi, buka tautan ini:
              </p>
              <p style="margin:0 0 12px; word-break:break-all; font-size:13px;">
                <a href="{{ route('my.payslip.view', ['token' => $h->view_token]) }}">
                  {{ route('my.payslip.view', ['token' => $h->view_token]) }}
                </a>
              </p>

              <p style="margin:0;">Terima kasih.</p>
            </td>
          </tr>

          <!-- Footer ringkas -->
          <tr>
            <td style="padding:16px 8px; text-align:center; color:#6b7280; font-size:12px;">
              © {{ now()->year }} BISA. Rahasiakan informasi penggajian Anda.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
