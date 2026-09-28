<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Sistem Perpustakaan Digital</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-slate-800" x-data="{
    fillCredentials(login, password) {
        $refs.loginInput.value = login;
        $refs.passwordInput.value = password;
    }
}">
    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-slate-900 text-blue-400 items-center justify-center shadow-xl shadow-slate-900/10 mb-4 border border-slate-800">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">PERPUS DUPAT</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem Perpustakaan Digital Jaringan LAN</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8">
            @if(session('success'))
                <div class="mb-5 p-3 rounded-xl bg-emerald-50 text-emerald-800 text-xs border border-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Login Identifier (NIS, NIP, or Email) -->
                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        NIS / NIP / Email
                    </label>
                    <input type="text"
                           name="login"
                           id="login"
                           x-ref="loginInput"
                           value="{{ old('login') }}"
                           required
                           autofocus
                           placeholder="Contoh: 19850101... atau 2024101"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-slate-50/50 @error('login') border-rose-500 @enderror">
                    @error('login')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi
                    </label>
                    <input type="password"
                           name="password"
                           id="password"
                           x-ref="passwordInput"
                           required
                           placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-slate-50/50 @error('password') border-rose-500 @enderror">
                    @error('password')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                        <span class="text-xs text-slate-600 font-medium">Ingat Saya</span>
                    </label>
                    <span class="text-xs text-slate-400">Node LAN Server</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm shadow-lg shadow-slate-900/10 transition active:scale-[0.99] flex items-center justify-center gap-2">
                    Masuk ke Sistem
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 text-center mb-2.5">
                    Demo Akun Pengujian (Klik untuk Isi):
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button"
                            @click="fillCredentials('admin', 'password')"
                            class="px-2.5 py-2 text-xs font-medium rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200/60 transition text-left">
                        <span class="block font-semibold">Pustakawan</span>
                        <span class="text-[10px] text-blue-600/80 font-mono">admin (NIP)</span>
                    </button>
                    <button type="button"
                            @click="fillCredentials('2024101', 'password')"
                            class="px-2.5 py-2 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60 transition text-left">
                        <span class="block font-semibold">Siswa (Ahmad)</span>
                        <span class="text-[10px] text-emerald-600/80 font-mono">2024101 (NIS)</span>
                    </button>
                </div>
            </div>

            <!-- Kiosk Station Quick Link -->
            <div class="mt-5 text-center">
                <a href="{{ route('kiosk.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 hover:text-emerald-700 hover:underline">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Menuju Kiosk Presensi Mandiri Siswa &rarr;
                </a>
            </div>
        </div>
    </div>
</body>
</html>
