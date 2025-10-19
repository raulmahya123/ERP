<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroal;
use App\Models\PayroalHistory;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class PayroalHistoryController extends Controller
{
    /**
     * List riwayat (filter periode & site)
     */
    public function index(Request $request)
    {
        // period dari <input type="month">, contoh: 2025-09 → simpan sebagai 2025-09-01
        $periodInput = (string) $request->query('period', '');
        $period = $periodInput ? date('Y-m-01', strtotime($periodInput)) : null;

        $site_id = $request->query('site_id');

        $rows = PayroalHistory::query()
            ->with([
                // ambil relasi berjenjang, bukan user langsung dari History
                'payroal:id,user_id,employee_code,site_id',
                'payroal.user' => fn($q) => $q->select('users.id', 'users.name', 'users.email'),
                'site:id,code,name',
            ])
            ->when($period, fn($q) => $q->whereDate('period', $period))
            ->when($site_id, fn($q) => $q->where('site_id', $site_id))
            ->orderByDesc('period')->orderBy('id')
            ->paginate(30);

        $sites = Site::orderBy('name')->get(['id', 'code', 'name']);

        return view('admin.payroal_history.index', compact('rows', 'period', 'site_id', 'sites'));
    }

    /**
     * Form generate bulk payslip (pilih periode & site)
     */
    public function create()
    {
        $sites = Site::orderBy('name')->get(['id', 'code', 'name']);
        return view('admin.payroal_history.create', compact('sites'));
    }

    /**
     * Generate payslip massal (draft) untuk semua payroal pada site/seluruh site
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'period'  => ['required', 'date'],         // gunakan tanggal 1 atau <input type="month">
            'site_id' => ['nullable', 'uuid', 'exists:sites,id'],
        ]);

        $period = date('Y-m-01', strtotime($data['period']));

        $payroals = Payroal::query()
            ->when($data['site_id'] ?? null, fn($q) => $q->where('site_id', $data['site_id']))
            ->with(['user' => fn($q) => $q->select('users.id', 'users.name', 'users.email')])
            ->get([
                'id',
                'user_id',
                'site_id',
                'base_salary',
                'allowance_meal',
                'allowance_transport',
                'allowance_position',
                'allowance_other'
            ]);

        DB::transaction(function () use ($payroals, $period) {
            foreach ($payroals as $p) {
                // lewati jika sudah ada payslip periode ini
                $exists = PayroalHistory::where('payroal_id', $p->id)
                    ->whereDate('period', $period)->exists();
                if ($exists) continue;

                // Contoh perhitungan sederhana; ganti sesuai formula Anda
                $gross = (float)($p->base_salary ?? 0)
                    + (float)($p->allowance_meal ?? 0)
                    + (float)($p->allowance_transport ?? 0)
                    + (float)($p->allowance_position ?? 0)
                    + (float)($p->allowance_other ?? 0);

                $deduction = 0; // isi PPh21/absen/dll di sini
                $net       = $gross - $deduction;

                PayroalHistory::create([
                    'id'             => (string) Str::uuid(),
                    'payroal_id'     => $p->id,
                    'period'         => $period,
                    'site_id'        => $p->site_id,
                    'gross'          => $gross,
                    'deduction'      => $deduction,
                    'net'            => $net,
                    'take_home_pay'  => $net,
                    'earnings'       => [
                        ['name' => 'Gaji Pokok', 'amount' => (float)($p->base_salary ?? 0)],
                        ['name' => 'Uang Makan', 'amount' => (float)($p->allowance_meal ?? 0)],
                        ['name' => 'Transport',  'amount' => (float)($p->allowance_transport ?? 0)],
                        ['name' => 'Jabatan',    'amount' => (float)($p->allowance_position ?? 0)],
                        ['name' => 'Lainnya',    'amount' => (float)($p->allowance_other ?? 0)],
                    ],
                    'deductions'     => [],
                    'status'         => 'draft',
                    'view_token'     => Str::random(48),
                ]);
            }
        });

        return redirect()->route('admin.payroal_history.index', ['period' => $period])
            ->with('success', 'Draft payslip berhasil dibuat untuk periode tersebut.');
    }

    /**
     * Lock payslip (mencegah perubahan sebelum kirim)
     */
    public function lock(PayroalHistory $history)
    {
        $history->update(['status' => 'locked', 'locked_at' => now()]);
        return back()->with('success', 'Payslip dikunci.');
    }

    /**
     * Kirim email satuan (sinkron). Ganti ke queue() jika ingin antrian.
     */
    public function sendOne(PayroalHistory $history)
    {
        if (!in_array($history->status, ['locked', 'sent'], true)) {
            return back()->withErrors(['status' => 'Kunci payslip terlebih dahulu (status=locked).']);
        }

        // Ambil user lewat payroal->user untuk menghindari join ambiguous
        $history->loadMissing(['payroal.user' => fn($q) => $q->select('users.id', 'users.name', 'users.email')]);
        $user = optional($history->payroal)->user;
        if (!$user || !$user->email) {
            return back()->withErrors(['email' => 'User tidak punya email.']);
        }

        // (Opsional) generate PDF/HTML dulu jika belum ada
        if (!$history->pdf_path) {
            $history->pdf_path = $this->generatePdf($history);
            $history->save();
        }

        try {
            // Pakai send() biar langsung terkirim tanpa worker
            Mail::to($user->email)->send(new \App\Mail\PayslipMail($history));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim payslip (sendOne)', [
                'history_id' => $history->id,
                'email'      => $user->email,
                'error'      => $e->getMessage(),
            ]);
            return back()->withErrors(['mail' => 'Gagal kirim email: ' . $e->getMessage()]);
        }

        $history->forceFill([
            'status'     => 'sent',
            'sent_at'    => now(),
            'emailed_to' => $user->email,
        ])->save();

        return back()->with('success', 'Payslip terkirim ke email karyawan.');
    }

    /**
     * Kirim email massal:
     * - Mode A: berdasarkan filter period (+ optional site)
     * - Mode B: berdasarkan checkbox "ids" (comma-separated)
     *
     * Form bisa kirim:
     *  - period=YYYY-MM & site_id (opsional), ATAU
     *  - ids=uuid1,uuid2,...
     */
    public function sendBulk(Request $request)
    {
        $idsRaw = trim((string) $request->input('ids', ''));
        $ids = collect($idsRaw === '' ? [] : explode(',', $idsRaw))
            ->map(fn($v) => trim($v))
            ->filter()
            ->unique()
            ->values();

        // ==== MODE B: by selected IDs ====
        if ($ids->isNotEmpty()) {
            $rows = PayroalHistory::query()
                ->with(['payroal.user:id,name,email'])
                ->whereIn('id', $ids)
                ->whereIn('status', ['locked', 'sent'])
                ->get();

            if ($rows->isEmpty()) {
                return back()->withErrors([
                    'bulk' => 'Tidak ada payslip yang memenuhi kriteria (pastikan status = Locked/Sent & user punya email).'
                ]);
            }

            $sent = 0;
            $skippedNoEmail = 0;
            $failed = 0;
            foreach ($rows as $history) {
                $user = optional($history->payroal)->user;
                if (!$user || !$user->email) {
                    $skippedNoEmail++;
                    continue;
                }

                if (!$history->pdf_path) {
                    $history->pdf_path = $this->generatePdf($history);
                    $history->save();
                }

                try {
                    \Mail::to($user->email)->send(new \App\Mail\PayslipMail($history));
                    $history->forceFill([
                        'status'     => 'sent',
                        'sent_at'    => now(),
                        'emailed_to' => $user->email,
                    ])->save();
                    $sent++;
                } catch (\Throwable $e) {
                    \Log::error('Gagal kirim payslip (sendBulk:ids)', [
                        'history_id' => $history->id,
                        'email' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                    $failed++;
                }
            }

            $msg = "Bulk send selesai. Terkirim: {$sent}";
            if ($skippedNoEmail > 0) $msg .= " | tanpa email: {$skippedNoEmail}";
            if ($failed > 0)        $msg .= " | gagal: {$failed} (lihat log)";
            return back()->with('success', $msg);
        }

        // ==== MODE A: by period (+ optional site) ====
        $periodInput = (string) $request->input('period', '');
        $siteId      = $request->input('site_id');

        if ($periodInput === '') {
            return back()->withErrors(['period' => 'Periode wajib diisi (YYYY-MM). Atau pilih baris dengan checkbox.']);
        }

        // Normalisasi period → YYYY-MM-01 (apapun inputnya)
        try {
            // support "YYYY-MM" atau "YYYY-MM-01" atau tanggal lengkap
            $ts     = strtotime($periodInput . (preg_match('/^\d{4}-\d{2}$/', $periodInput) ? '-01' : ''));
            $period = date('Y-m-01', $ts ?: time());
        } catch (\Throwable $e) {
            return back()->withErrors(['period' => 'Format periode tidak valid. Gunakan YYYY-MM.']);
        }

        // === DIAGNOSTIK: kasih tau kenapa nol ===
        $totalThisPeriod = PayroalHistory::whereDate('period', $period)
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->count();

        $lockedOrSentThisPeriod = PayroalHistory::whereDate('period', $period)
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->whereIn('status', ['locked', 'sent'])
            ->count();

        $withEmailThisPeriod = PayroalHistory::whereDate('period', $period)
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->whereIn('status', ['locked', 'sent'])
            ->whereHas('payroal.user', fn($q) => $q->whereNotNull('email')->where('email', '!=', ''))
            ->count();

        $rows = PayroalHistory::query()
            ->with(['payroal.user:id,name,email'])
            ->whereDate('period', $period)
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->whereIn('status', ['locked', 'sent'])
            ->get();

        if ($rows->isEmpty()) {
            // Pesan detail supaya ketahuan masalahnya
            $why = [];
            $why[] = "total periode: {$totalThisPeriod}";
            $why[] = "locked/sent: {$lockedOrSentThisPeriod}";
            $why[] = "punya email: {$withEmailThisPeriod}";
            return back()->withErrors([
                'bulk' => 'Tidak ada payslip yang bisa dikirim untuk filter ini. Rincian: ' . implode(' • ', $why) .
                    '. Pastikan payslip sudah di-LOCK dan user punya email.'
            ]);
        }

        $sent = 0;
        $skippedNoEmail = 0;
        $failed = 0;
        foreach ($rows as $history) {
            $user = optional($history->payroal)->user;
            if (!$user || !$user->email) {
                $skippedNoEmail++;
                continue;
            }

            if (!$history->pdf_path) {
                $history->pdf_path = $this->generatePdf($history);
                $history->save();
            }

            try {
                \Mail::to($user->email)->send(new \App\Mail\PayslipMail($history));
                $history->forceFill([
                    'status'     => 'sent',
                    'sent_at'    => now(),
                    'emailed_to' => $user->email,
                ])->save();
                $sent++;
            } catch (\Throwable $e) {
                \Log::error('Gagal kirim payslip (sendBulk:period)', [
                    'history_id' => $history->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $msg = "Bulk send selesai. Terkirim: {$sent}";
        if ($skippedNoEmail > 0) $msg .= " | tanpa email: {$skippedNoEmail}";
        if ($failed > 0)        $msg .= " | gagal: {$failed} (lihat log)";
        return back()->with('success', $msg);
    }


    /**
     * Util: generate "PDF" placeholder sebagai HTML snapshot.
     * Ganti ke engine PDF (dompdf/snappy) jika diperlukan.
     */
    private function generatePdf(PayroalHistory $history): string
    {
        // Jika 'period' belum dicast ke date di model, pakai strtotime agar aman.
        $yyyymm = date('Ym', strtotime($history->period));

        // Placeholder tanpa lib PDF — simpan HTML snapshot:
        $path = "payslips/{$yyyymm}/{$history->id}.html";
        $html = view('pdf.payslip-lite', ['h' => $history])->render();
        Storage::disk('local')->put($path, $html);
        return $path;

        // Jika ingin PDF beneran (contoh dompdf):
        // $pdf = \PDF::loadView('pdf.payslip', ['h'=>$history])->setPaper('A4','portrait');
        // Storage::disk('local')->put($path = "payslips/{$yyyymm}/{$history->id}.pdf", $pdf->output());
        // return $path;
    }
}
