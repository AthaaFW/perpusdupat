@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    checkinModal: false,
    nis: '',
    purpose: 'Membaca',
    notes: '',
    submitVisit() {
        fetch('{{ route('visits.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Alpine-Request': 'true'
            },
            body: JSON.stringify({
                nis_nip: this.nis,
                purpose: this.purpose,
                notes: this.notes
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Alpine.store('toast').notify(data.message, 'success');
                this.checkinModal = false;
                setTimeout(() => window.location.reload(), 1000);
            } else {
                Alpine.store('toast').notify(data.message || 'Gagal menyimpan presensi', 'error');
            }
        })
        .catch(() => {
            Alpine.store('toast').notify('Koneksi LAN terputus', 'error');
        });
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                {{ auth()->user()->isPustakawan() ? 'Log Kunjungan Perpustakaan' : 'Riwayat Kunjungan Saya' }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ auth()->user()->isPustakawan() ? 'Rekapitulasi presensi kehadiran siswa dan civitas sekolah di perpustakaan.' : 'Catatan riwayat kunjungan dan kehadiran Anda di perpustakaan.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="checkinModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Catat Kunjungan
            </button>

            @if(auth()->user()->isPustakawan())
                <a href="{{ route('visits.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs border border-slate-300 shadow-xs transition" title="Ekspor laporan siap cetak ke format Excel">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Cetak Laporan (XLSX)
                </a>
            @endif
        </div>
    </div>

    <!-- Filter Bar (Librarian) -->
    @if(auth()->user()->isPustakawan())
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('visits.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Pencarian Siswa</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama siswa atau NIS..." class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs transition">
                        Terapkan Filter
                    </button>
                    @if($search || request('start_date') || request('end_date'))
                        <a href="{{ route('visits.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Reset Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    @endif

    <!-- Visits Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">NIS / NIP</th>
                        <th class="py-3 px-4">Nama Pengunjung</th>
                        <th class="py-3 px-4 text-center">Tujuan</th>
                        <th class="py-3 px-4">Catatan</th>
                        @if(auth()->user()->isPustakawan())
                            <th class="py-3 px-4 text-center w-16">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visits as $index => $visit)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-mono">
                                {{ $visits->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($visit->visit_date)->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $visit->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-medium text-slate-700">
                                {{ $visit->user?->nis_nip ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900">{{ $visit->user?->name ?? 'Tamu' }}</div>
                                <div class="text-[10px] text-slate-400 capitalize">{{ $visit->user?->role ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $visit->purpose }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                {{ $visit->notes ?: '-' }}
                            </td>
                            @if(auth()->user()->isPustakawan())
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('visits.destroy', $visit) }}" onsubmit="return confirm('Hapus log kunjungan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="Hapus Log">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isPustakawan() ? 7 : 6 }}" class="py-12 text-center text-slate-400">
                                Belum ada data kunjungan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $visits->links() }}
            </div>
        @endif
    </div>

    <!-- Manual Visit Check-in Modal -->
    <div x-cloak x-show="checkinModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="checkinModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Catat Kunjungan Baru</h3>
                <button @click="checkinModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <div class="space-y-4">
                @if(auth()->user()->isPustakawan())
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIS / NIP Siswa <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="nis" placeholder="Contoh: 2024101" class="w-full px-3 py-2 border rounded-xl text-xs font-mono">
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tujuan Kunjungan</label>
                    <select x-model="purpose" class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                        <option value="Membaca">Membaca Buku / Referensi</option>
                        <option value="Meminjam">Meminjam / Mengembalikan Buku</option>
                        <option value="Tugas">Mengerjakan Tugas</option>
                        <option value="Diskusi">Diskusi Belajar Kelompok</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan (Opsional)</label>
                    <input type="text" x-model="notes" placeholder="Catatan kunjungan..." class="w-full px-3 py-2 border rounded-xl text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="checkinModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="button" @click="submitVisit()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-sm transition">
                        Simpan Presensi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
