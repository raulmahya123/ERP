<?php

// app/Listeners/SendEmailVerificationCode.php
namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Models\EmailVerificationCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Support\Carbon;

class SendEmailVerificationCode
{
    public function handle(Registered $event): void
    {
        $user = $event->user;
        if ($user->email_verified_at) return;

        $ttl = (int) config('auth.verify_code_ttl', 10);
        $now = now();

        DB::transaction(function () use ($user, $ttl, $now) {
            // Buat/replace kode
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            EmailVerificationCode::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'code'       => $code,
                    'attempts'   => 0,
                    'expires_at' => Carbon::parse($now)->addMinutes($ttl),
                ]
            );

            // Kirim email langsung (non-queue)
            $user->notify(new EmailVerificationCodeNotification($code, $ttl));
        });
    }
}
