<?php

// app/Http/Controllers/Auth/VerifyEmailCodeController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerifyEmailCodeController extends Controller
{
    public function create(Request $request)
    {
        // Form input OTP
        return view('auth.verify-code');
    }

    public function store(Request $request)
    {
        $request->validate(['code' => ['required','digits:6']]);
        $user = Auth::user();

        $row = EmailVerificationCode::where('user_id', $user->id)->lockForUpdate()->first();
        if (!$row) throw ValidationException::withMessages(['code' => 'Kode tidak ditemukan. Minta ulang kode.']);

        if ($row->isExpired()) {
            throw ValidationException::withMessages(['code' => 'Kode kedaluwarsa. Silakan kirim ulang.']);
        }

        // Batas salah sederhana
        if ($row->attempts >= 5) {
            throw ValidationException::withMessages(['code' => 'Terlalu banyak percobaan. Kirim ulang kode.']);
        }

        if ($request->code !== $row->code) {
            $row->increment('attempts');
            throw ValidationException::withMessages(['code' => 'Kode salah.']);
        }

        // Sukses: verifikasi email & hapus kode
        DB::transaction(function () use ($user, $row) {
            $user->forceFill(['email_verified_at' => now()])->save();
            $row->delete();
        });

        return redirect()->intended(route('dashboard'))->with('status','Email terverifikasi.');
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        // throttle sederhana 60s
        $last = EmailVerificationCode::where('user_id',$user->id)->first();
        if ($last && now()->diffInSeconds($last->updated_at) < 60) {
            return back()->with('error','Tunggu beberapa detik sebelum kirim ulang.');
        }

        event(new \Illuminate\Auth\Events\Registered($user)); // trigger listener kirim kode
        return back()->with('status','Kode baru sudah dikirim ke email Anda.');
    }
}
