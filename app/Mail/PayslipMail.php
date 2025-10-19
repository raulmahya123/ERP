<?php

namespace App\Mail;

use App\Models\PayroalHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PayroalHistory $history) {}

    public function build()
    {
        $h = $this->history;

        $subj = 'Payslip ' . (
            method_exists($h->period, 'format')
                ? $h->period->format('F Y')
                : date('F Y', strtotime((string)$h->period))
        );

        $mail = $this->subject($subj)
            ->view('emails.payslip', ['h' => $h]);

        // lampiran PDF kalau ada
        if ($h->pdf_path && Storage::disk('local')->exists($h->pdf_path)) {
            $mail->attach(
                Storage::disk('local')->path($h->pdf_path),
                [
                    'as'   => 'Payslip-' . date('Ym', strtotime((string)$h->period)) . '.pdf',
                    'mime' => 'application/pdf',
                ]
            );
        }

        return $mail;
    }
}
