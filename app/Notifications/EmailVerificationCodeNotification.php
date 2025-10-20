<?php

// app/Notifications/EmailVerificationCodeNotification.php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationCodeNotification extends Notification
{
    public function __construct(public string $code, public int $ttlMinutes = 10) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        $app = config('app.name', 'HUMAN Careers');
        return (new MailMessage)
            ->subject("Kode Verifikasi $app")
            ->greeting('Kode Verifikasi Anda')
            ->line("Masukkan kode ini untuk verifikasi email:")
            ->line("**{$this->code}**")
            ->line("Kode berlaku {$this->ttlMinutes} menit.")
            ->line('Jika tidak meminta kode ini, abaikan email ini.');
    }
}
