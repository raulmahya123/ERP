{{-- resources/views/auth/forgot-password.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Lupa Password — {{ config('app.name', 'BISA') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="h-full">
    <div class="min-h-full grid place-items-center py-10 px-4">
        <div class="w-full max-w-md" x-data="{ submitting:false }">
            <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-2xl p-6">

                {{-- Header --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white grid place-items-center shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zm0 2c-3.314 0-6 2.013-6 4.5V19h12v-1.5c0-2.487-2.686-4.5-6-4.5z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900">Lupa Password</h1>
                        <p class="text-sm text-slate-600">Masukkan email Anda. Kami akan kirim tautan untuk mengatur ulang password.</p>
                    </div>
                </div>

                {{-- Status sukses --}}                </div>
                @endif

                {{-- Errors global --}}
                @if ($errors->any())
                <div class="mt-4 rounded-lg bg-rose-50 text-rose-800 ring-1 ring-rose-200 px-3 py-2 text-sm">
                    @foreach ($errors->all() as $err)
                    <div>• {{ $err }}</div>
                    @endforeach
                </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('password.email') }}" class="mt-6">
                    @csrf

                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="mt-1 w-full rounded-xl ring-1 ring-slate-300 px-3 py-2 focus:ring-2 focus:ring-violet-500 outline-none" />

                    @error('email')
                    <div class="mt-2 rounded-lg bg-rose-50 text-rose-800 ring-1 ring-rose-200 px-3 py-2 text-sm">
                        {{ $message }}
                    </div>
                    @enderror

                    <button type="submit"
                        class="mt-4 w-full inline-flex justify-center items-center rounded-xl bg-violet-600 text-white font-semibold py-2.5 hover:bg-violet-700">
                        Kirim Tautan Reset
                    </button>
                </form>

                {{-- Bantuan: tidak terima email? --}}
                <div class="mt-5 rounded-xl bg-slate-50 ring-1 ring-slate-200 px-3 py-3">
                    <div class="text-xs text-slate-600">
                        <div class="font-semibold text-slate-700 mb-1">Tidak menerima email?</div>
                        <ul class="list-disc ml-4 space-y-1">
                            <li>Cek folder <span class="font-medium">Spam/Junk</span> dan <span class="font-medium">Promotions</span>.</li>
                            <li>Pastikan alamat <span class="font-mono">{{ config('mail.from.address') }}</span> di-whitelist.</li>
                            <li>Kirim ulang setelah beberapa menit bila Anda melihat pesan “terlalu banyak percobaan”.</li>
                            <li>Jika tetap tidak masuk, hubungi admin Anda.</li>
                        </ul>
                    </div>
                    @env('local')
                    <div class="mt-2 text-[11px] text-amber-700 bg-amber-50 ring-1 ring-amber-200 rounded-lg px-2 py-1.5">
                        <span class="font-semibold">Dev hint:</span> pastikan <span class="font-mono">MAIL_MAILER</span> & SMTP di <span class="font-mono">.env</span> benar, atau gunakan <span class="font-mono">log</span> mailer.
                    </div>
                    @endenv
                </div>

                {{-- Links --}}
                <div class="mt-4 text-xs text-slate-500 flex items-center justify-between">
                    <a href="{{ route('login') }}" class="underline font-semibold">Kembali ke Login</a>
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="underline">Daftar akun</a>
                    @endif
                </div>
            </div>

            <div class="text-center text-xs text-slate-400 mt-4">
                &copy; {{ date('Y') }} {{ config('app.name', 'BISA') }}
            </div>
        </div>
    </div>
</body>

</html>