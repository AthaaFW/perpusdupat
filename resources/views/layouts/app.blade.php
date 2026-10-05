<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF8F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Sistem Perpustakaan Digital' }} - Perpus Dupat</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|newsreader:400,500,600,700|lora:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-stone-800 bg-[#FAF8F5] flex flex-col" x-data>
    <!-- Top Navigation Bar (Bookhaven Deep Forest Palette) -->
    <header class="bg-[#183527] border-b border-[#224A35] text-[#FAF8F5] sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center gap-6">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-[#224A35] border border-[#2F6147] flex items-center justify-center font-bold text-[#E5C158] shadow-sm group-hover:bg-[#2A5A41] transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-serif font-bold text-base tracking-wide text-[#FAF8F5] leading-tight">PERPUS DUPAT</div>
                            <div class="text-[11px] text-[#A3B899] font-medium leading-none">Perpustakaan Digital Terpadu</div>
                        </div>
                    </a>

                    <!-- Nav Links -->
                    <nav class="hidden md:flex items-center gap-1 ml-4">
                        @if(auth()->user()->isPustakawan())
                            <a href="{{ route('librarian.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('librarian.dashboard') ? 'bg-[#224A35] text-[#FAF8F5] border border-[#2F6147]' : 'text-[#C2D6C0] hover:text-[#FAF8F5] hover:bg-[#224A35]/60' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('books.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('books.*') ? 'bg-[#224A35] text-[#FAF8F5] border border-[#2F6147]' : 'text-[#C2D6C0] hover:text-[#FAF8F5] hover:bg-[#224A35]/60' }}">
                                Katalog Buku
                            </a>
                            <a href="{{ route('loans.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('loans.*') ? 'bg-[#224A35] text-[#FAF8F5] border border-[#2F6147]' : 'text-[#C2D6C0] hover:text-[#FAF8F5] hover:bg-[#224A35]/60' }}">
                                Sirkulasi Peminjaman
                            </a>
                            <a href="{{ route('visits.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('visits.*') ? 'bg-[#224A35] text-[#FAF8F5] border border-[#2F6147]' : 'text-[#C2D6C0] hover:text-[#FAF8F5] hover:bg-[#224A35]/60' }}">
                                Kunjungan Siswa
                            </a>
                        @else
                            <a href="{{ route('student.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.dashboard') ? 'bg-[#224A35] text-[#FAF8F5] border border-[#2F6147]' : 'text-[#C2D6C0] hover:text-[#FAF8F5] hover:bg-[#224A35]/60' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('books.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('books.*') ? 'bg-[#224A35] text-[#FAF8F5] border border-[#2F6147]' : 'text-[#C2D6C0] hover:text-[#FAF8F5] hover:bg-[#224A35]/60' }}">
                                Jelajah Buku
                            </a>
                            <a href="{{ route('loans.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('loans.*') ? 'bg-[#224A35] text-[#FAF8F5] border border-[#2F6147]' : 'text-[#C2D6C0] hover:text-[#FAF8F5] hover:bg-[#224A35]/60' }}">
                                Pinjaman Saya
                            </a>
                            <a href="{{ route('visits.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('visits.*') ? 'bg-[#224A35] text-[#FAF8F5] border border-[#2F6147]' : 'text-[#C2D6C0] hover:text-[#FAF8F5] hover:bg-[#224A35]/60' }}">
                                Riwayat Kunjungan
                            </a>
                        @endif

                        <a href="{{ route('kiosk.index') }}" target="_blank" class="px-3 py-2 rounded-lg text-sm font-medium text-[#E5C158] hover:bg-[#224A35]/60 flex items-center gap-1.5 transition">
                            <span class="w-2 h-2 rounded-full bg-[#E5C158] animate-pulse"></span>
                            Buka Kiosk Mandiri
                        </a>
                    </nav>
                </div>

                <!-- Right Actions: LAN Status & User Profile -->
                <div class="flex items-center gap-3">
                    <!-- LAN Badge -->
                    <div class="hidden lg:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#224A35]/70 border border-[#2F6147] text-xs text-[#C2D6C0]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>LAN Mode</span>
                    </div>

                    <!-- User Menu Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-[#224A35] transition focus:outline-none">
                            <div class="w-8 h-8 rounded-lg bg-[#224A35] text-[#E5C158] border border-[#2F6147] flex items-center justify-center font-bold text-xs uppercase">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="text-xs font-semibold text-white leading-none">{{ auth()->user()->name }}</div>
                                <div class="text-[10px] text-[#A3B899] capitalize mt-0.5">
                                    {{ auth()->user()->role }} &bull; {{ auth()->user()->nis_nip }}
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-[#A3B899]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-xl border border-[#E7E3DA] py-1.5 text-sm text-stone-700 z-50 divide-y divide-[#F0ECE1]" style="display: none;">
                            <div class="px-4 py-2.5">
                                <div class="font-medium text-stone-900 text-xs truncate">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-stone-500 font-mono mt-0.5">NIS/NIP: {{ auth()->user()->nis_nip }}</div>
                                <span class="inline-flex mt-1.5 items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ auth()->user()->isPustakawan() ? 'bg-[#EAEFD9] text-[#2B5941] border border-[#C6D8B3]' : 'bg-[#FDF4E7] text-[#9A6A1E] border border-[#F3DFC1]' }}">
                                    {{ strtoupper(auth()->user()->role) }}
                                </span>
                            </div>

                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Keluar (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-5 rounded-xl border border-[#C6D8B3] bg-[#EAEFD9]/90 p-4 text-[#2B5941] flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-[#2B5941] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 rounded-xl border border-[#F4D1CA] bg-[#FBF0ED]/90 p-4 text-[#B84233] flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-[#B84233] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-5 rounded-xl border border-[#F3DFC1] bg-[#FDF4E7]/90 p-4 text-[#9A6A1E] flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-[#9A6A1E] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <div class="text-sm font-medium">{{ session('warning') }}</div>
                    @if(session('import_errors'))
                        <div class="mt-2 text-xs bg-white/70 p-2.5 rounded-lg border border-[#F3DFC1] max-h-40 overflow-y-auto font-mono">
                            @foreach(session('import_errors') as $err)
                                <div>• Baris {{ $err['row'] }} (ISBN: {{ $err['isbn'] }}): {{ $err['message'] }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Global Toast Notification Widget (Alpine.js) -->
    <div x-cloak x-show="$store.toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-2 scale-95" class="fixed bottom-5 right-5 z-50 max-w-sm w-full">
        <div class="rounded-xl shadow-2xl p-4 flex items-center justify-between text-white border"
             :class="{
                'bg-[#2B5941] border-[#1F4531]': $store.toast.type === 'success',
                'bg-[#B84233] border-[#9A3528]': $store.toast.type === 'error',
                'bg-[#A26D24] border-[#89591A]': $store.toast.type === 'warning',
                'bg-[#183527] border-[#11271D]': $store.toast.type === 'info'
             }">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium" x-text="$store.toast.message"></div>
            </div>
            <button @click="$store.toast.close()" class="text-white/80 hover:text-white p-1 rounded-md transition focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Minimalist Editorial Footer -->
    <footer class="border-t border-[#E7E3DA] bg-[#FAF8F5] py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-stone-500">
            Sistem Perpustakaan Digital &bull; Jaringan LAN Terpadu &bull; Server AMD A4 Low-Memory Optimized
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
