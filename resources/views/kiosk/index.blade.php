<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kiosk Presensi Mandiri - Perpus Dupat</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-100 flex flex-col justify-between p-4 sm:p-6 lg:p-8 select-none"
      x-data="{
        nis: '',
        purpose: 'Membaca',
        isLoading: false,
        totalToday: {{ $visitCountToday }},
        recentVisits: {{ json_encode($todayVisits->map(function($v) {
            return [
                'id' => $v->id,
                'name' => $v->user?->name ?? 'Tamu',
                'nis' => $v->user?->nis_nip ?? '-',
                'purpose' => $v->purpose,
                'time' => $v->created_at->format('H:i')
            ];
        })) }},
        currentTime: '',
        currentDate: '',

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.$nextTick(() => {
                this.$refs.nisInput.focus();
            });
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        },

        setPurpose(p) {
            this.purpose = p;
            this.$refs.nisInput.focus();
        },

        submitCheckIn() {
            if (!this.nis.trim()) {
                Alpine.store('toast').notify('Harap masukkan nomor NIS Anda.', 'warning');
                this.$refs.nisInput.focus();
                return;
            }

            this.isLoading = true;

            fetch('{{ route('kiosk.check-in') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nis: this.nis.trim(),
                    purpose: this.purpose
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isLoading = false;
                if (data.success) {
                    Alpine.store('toast').notify(data.message, 'success');
                    
                    // Prepend to live visitor queue
                    this.recentVisits.unshift(data.data);
                    if (this.recentVisits.length > 15) {
                        this.recentVisits.pop();
                    }
                    this.totalToday = data.data.total_today;
                    this.nis = '';
                } else {
                    Alpine.store('toast').notify(data.message || 'NIS tidak ditemukan.', 'error');
                }
                this.$refs.nisInput.focus();
            })
            .catch(() => {
                this.isLoading = false;
                Alpine.store('toast').notify('Gagal menghubungi server LAN perpustakaan.', 'error');
                this.$refs.nisInput.focus();
            });
        }
      }">

    <!-- Top Header -->
    <header class="flex items-center justify-between border-b border-slate-800/80 pb-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                    PERPUS DUPAT
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        KIOSK SISWA
                    </span>
                </h1>
                <p class="text-xs text-slate-400">Stasiun Mandiri Presensi Pengunjung Perpustakaan (Client Node LAN)</p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <!-- Clock -->
            <div class="text-right hidden sm:block">
                <div class="text-2xl font-mono font-bold text-white tracking-wider" x-text="currentTime"></div>
                <div class="text-[11px] text-slate-400" x-text="currentDate"></div>
            </div>

            <!-- Portal Login Link -->
            <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-700/80 text-xs font-semibold transition flex items-center gap-2">
                <span>Masuk Portal Siswa</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="my-auto py-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start max-w-7xl w-full mx-auto">
        <!-- Left: Check-in Box -->
        <div class="lg:col-span-6 bg-slate-900/90 rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl shadow-black/40 space-y-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
                    Stasiun Presensi Cepat
                </div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Presensi Kunjungan Anda</h2>
                <p class="text-xs text-slate-400 mt-1">Tempelkan barcode kartu atau ketik Nomor Induk Siswa (NIS), lalu tekan Enter.</p>
            </div>

            <!-- NIS Form -->
            <form @submit.prevent="submitCheckIn()" class="space-y-5">
                <div>
                    <label for="kiosk-nis" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Nomor Induk Siswa (NIS)
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="kiosk-nis"
                               x-ref="nisInput"
                               x-model="nis"
                               required
                               autocomplete="off"
                               placeholder="Contoh: 2024101"
                               class="w-full px-5 py-4 rounded-2xl bg-slate-950 border-2 border-slate-700 text-xl font-mono text-emerald-400 placeholder:text-slate-600 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 focus:outline-none transition shadow-inner">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 font-mono text-xs">
                            [Tekan Enter]
                        </div>
                    </div>
                </div>

                <!-- Purpose Selector -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Tujuan Kunjungan
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @php
                            $purposes = [
                                'Membaca' => 'Membaca',
                                'Meminjam' => 'Pinjam / Balik',
                                'Tugas' => 'Tugas',
                                'Diskusi' => 'Diskusi',
                            ];
                        @endphp
                        @foreach($purposes as $val => $label)
                            <button type="button"
                                    @click="setPurpose('{{ $val }}')"
                                    :class="purpose === '{{ $val }}' ? 'bg-blue-600 text-white border-blue-500 shadow-md shadow-blue-600/30' : 'bg-slate-950/80 text-slate-400 border-slate-800 hover:border-slate-700'"
                                    class="py-3 px-3 rounded-xl border text-xs font-bold transition text-center focus:outline-none">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        :disabled="isLoading"
                        class="w-full py-4 px-6 rounded-2xl bg-emerald-500 hover:bg-emerald-400 active:scale-[0.99] text-slate-950 font-bold text-base shadow-xl shadow-emerald-500/20 transition flex items-center justify-center gap-3">
                    <span x-show="isLoading" class="w-5 h-5 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isLoading ? 'Mencatat Kehadiran...' : 'Konfirmasi Kehadiran (Check-In)'"></span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <div class="pt-4 border-t border-slate-800 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Contoh NIS demo: <code class="text-slate-300 font-mono">2024101</code>, <code class="text-slate-300 font-mono">2024102</code></span>
                <span>Subnet LAN Mode</span>
            </div>
        </div>

        <!-- Right: Real-time Visitor Stream -->
        <div class="lg:col-span-6 bg-slate-900/90 rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl shadow-black/40 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white">Antrean Kunjungan Terkini</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar kehadiran hari ini secara real-time</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 uppercase font-semibold">Total Hari Ini</span>
                    <div class="text-2xl font-bold font-mono text-emerald-400" x-text="totalToday + ' Siswa'"></div>
                </div>
            </div>

            <!-- List of visitors -->
            <div class="divide-y divide-slate-800/80 max-h-[380px] overflow-y-auto pr-1">
                <template x-for="item in recentVisits" :key="item.id">
                    <div class="py-3 flex items-center justify-between hover:bg-slate-800/30 rounded-xl px-2 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-800 text-blue-400 flex items-center justify-center font-bold text-xs uppercase border border-slate-700/60">
                                <span x-text="item.name ? item.name.substring(0, 2) : 'S'"></span>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white" x-text="item.name"></div>
                                <div class="text-[11px] text-slate-400">NIS: <span class="font-mono text-slate-300" x-text="item.nis"></span></div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30" x-text="item.purpose"></span>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5" x-text="item.time + ' WIB'"></div>
                        </div>
                    </div>
                </template>

                <template x-if="recentVisits.length === 0">
                    <div class="py-12 text-center text-xs text-slate-500">
                        Belum ada siswa yang presensi hari ini. Jadilah yang pertama!
                    </div>
                </template>
            </div>
        </div>
    </main>

    <!-- Global Toast for Kiosk Notifications -->
    <div x-cloak x-show="$store.toast.show" x-transition class="fixed top-8 left-1/2 -translate-x-1/2 z-50 max-w-md w-full px-4">
        <div class="rounded-2xl shadow-2xl p-4 flex items-center justify-between text-white border"
             :class="{
                'bg-emerald-600 border-emerald-500': $store.toast.type === 'success',
                'bg-rose-600 border-rose-500': $store.toast.type === 'error',
                'bg-amber-600 border-amber-500': $store.toast.type === 'warning',
                'bg-blue-600 border-blue-500': $store.toast.type === 'info'
             }">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-bold" x-text="$store.toast.message"></div>
            </div>
            <button @click="$store.toast.close()" class="p-1 rounded-lg text-white/80 hover:text-white">&times;</button>
        </div>
    </div>

    <!-- Kiosk Footer -->
    <footer class="border-t border-slate-900 pt-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-2">
        <div>
            Stasiun Kiosk Mandiri &bull; Perpustakaan Digital SMA/SMK Negeri &bull; Jalur Subnet LAN
        </div>
        <div>
            Tekan <kbd class="px-1.5 py-0.5 bg-slate-800 text-slate-300 rounded border border-slate-700 text-[10px] font-mono">F11</kbd> untuk Tampilan Layar Penuh (Kiosk Mode)
        </div>
    </footer>
</body>
</html>
