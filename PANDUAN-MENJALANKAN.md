# Panduan Menjalankan Perpus Dupat di Windows

Panduan instalasi awal, penggunaan harian, dan pemecahan masalah berdasarkan pendampingan pada 5 Oktober 2026. Gunakan PowerShell di Antigravity, VS Code, atau Windows Terminal. Folder proyek dalam panduan ini adalah `D:\perpus24\perpusdupat`.

Perpus Dupat merupakan aplikasi Laravel. Jalankan melalui server PHP, bukan dengan membuka file HTML. Penjelasan fitur dan struktur kode tersedia di [README](README.md).

## 1. Persiapan repository

Fork membuat salinan repository di akun GitHub; clone mengunduhnya ke laptop. Keduanya tidak memasang dependensi aplikasi.

- Repository asal: [dimcoord/perpusdupat](https://github.com/dimcoord/perpusdupat).
- Fork pengguna: [tiaraadhanie1001-art/perpusdupat](https://github.com/tiaraadhanie1001-art/perpusdupat).

Jika proyek sudah ada di laptop, tidak perlu clone lagi:

```powershell
cd D:\perpus24\perpusdupat
git remote -v
```

Pada folder yang diperiksa, `origin` sudah menunjuk fork pengguna dan `upstream` menunjuk repository asal. Keduanya adalah nama remote (alamat repository), bukan branch. Gunakan `git branch` untuk melihat branch.

Untuk laptop baru, pasang Git, kemudian clone ke folder tujuan yang belum berisi proyek:

```powershell
git clone https://github.com/tiaraadhanie1001-art/perpusdupat.git D:\perpus24\perpusdupat
cd D:\perpus24\perpusdupat
git remote add upstream https://github.com/dimcoord/perpusdupat.git
```

Ganti alamat fork jika menggunakan akun berbeda. Jika belum memiliki fork, buka repository asal di GitHub, klik **Fork**, pilih akun sendiri, lalu **Create fork**. Jangan menjalankan `remote add` ulang jika namanya sudah ada. Lihat [panduan fork GitHub](https://docs.github.com/en/pull-requests/how-tos/work-with-forks/fork-a-repo?tool=webui).

## 2. Siapkan perangkat pendukung

| Perangkat | Fungsi |
| --- | --- |
| PHP | Menjalankan backend Laravel |
| Composer | Memasang dependensi PHP ke folder `vendor` |
| Node.js dan npm | Memasang dan membangun aset tampilan |
| Laravel Herd | Menyediakan lingkungan PHP dan alat pengembangan Windows |

Proyek mensyaratkan PHP `^8.3`. Vite pada lock file membutuhkan Node.js `^20.19.0` atau `>=22.12.0`. Composer akan memeriksa persyaratan seluruh dependensi saat instalasi.

1. Unduh installer dari [Laravel Herd untuk Windows](https://herd.laravel.com/windows).
2. Selesaikan instalasi dan klik **Finish**.
3. Buka aplikasi **Laravel Herd** melalui Start, bukan installer-nya.
4. Selesaikan pengaturan awal dan tunggu unduhan komponennya selesai.
5. Tutup seluruh jendela editor dan PowerShell, lalu buka kembali agar PATH terbaru terbaca.
6. Periksa satu per satu:

```powershell
php -v
composer --version
node -v
npm -v
```

**Tanda siap:** setiap perintah menampilkan nomor versi. Kembali ke prompt tanpa keluaran bukan bukti berhasil. Jika terjadi error atau keluaran kosong, selesaikan masalah pada bagian 6 terlebih dahulu.

Jika Node.js/npm tetap belum tersedia, gunakan installer Windows dari [Node.js](https://nodejs.org/en/download) dengan versi yang memenuhi kebutuhan proyek, kemudian buka ulang editor.

Pada kasus kita, PHP 8.4.26 diblokir Windows. Pemeriksaan PHP 8.3.35 berhasil, dengan penanganan OPcache seperti dijelaskan pada bagian 6.

## 3. Instalasi proyek dari awal

Jalankan satu tahap sampai selesai sebelum melanjutkan. Jangan mengetik awalan prompt seperti `PS D:\...>`; ketik perintahnya saja.

### Langkah 1 — Masuk ke folder proyek

```powershell
cd D:\perpus24\perpusdupat
```

Pastikan folder ini berisi `artisan`, `composer.json`, dan `package.json`.

### Langkah 2 — Pasang dependensi backend

```powershell
composer install
```

Tunggu selesai tanpa error, lalu periksa:

```powershell
Test-Path vendor/autoload.php
```

Hasil harus `True`. Keberadaan file ini hanya pemeriksaan dasar; proses Composer tetap harus sukses. Jangan mengganti `install` dengan `update` untuk mengatasi error instalasi karena `update` dapat mengubah versi dependensi.

### Langkah 3 — Siapkan konfigurasi lokal

```powershell
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
}
```

Buka **`.env`**, bukan `.env.example`. File `.env.example` adalah template; `.env` adalah konfigurasi lokal aplikasi. Pastikan:

```dotenv
DB_CONNECTION=sqlite
```

Dengan konfigurasi SQLite bawaan, tidak perlu menyalakan MySQL. Jangan membagikan isi lengkap `.env` atau mengunggahnya ke GitHub.

Jika `APP_KEY=` masih kosong pada instalasi baru, jalankan:

```powershell
php artisan key:generate --no-interaction
```

Pertahankan kunci yang sudah ada. Tidak perlu membuat kunci baru setiap kali membuka aplikasi.

### Langkah 4 — Buat database dan tabel

```powershell
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File -Path database/database.sqlite
}

php artisan migrate --no-interaction
```

File SQLite saja belum berarti tabelnya sudah dibuat. Migrasi membuat tabel aplikasi, termasuk tabel session dan cache. Pesan `Nothing to migrate` berarti tidak ada migrasi baru yang perlu dijalankan.

### Langkah 5 — Isi data demo sekali

Untuk database baru:

```powershell
php artisan db:seed --no-interaction
```

Seeder mengisi akun, kategori, buku, contoh kunjungan/pinjaman, sampul, dan token scanner. **Lewati jika sudah pernah berhasil pada database ini.** Pengulangan menambahkan transaksi contoh dan mengganti token scanner lama.

Hindari `migrate:fresh` atau `library:init --fresh` pada database yang ingin dipertahankan karena akan menghapus tabel sebelum membuatnya kembali.

### Langkah 6 — Hubungkan sampul buku

```powershell
php artisan storage:link --no-interaction
```

Jika link sudah ada, lanjutkan. Perintah ini memungkinkan sampul di `storage/app/public` diakses melalui folder publik.

### Langkah 7 — Pasang dependensi tampilan dan build

```powershell
npm ci --ignore-scripts
npm run build
```

Tunggu kedua proses selesai. Build berhasil jika muncul pesan seperti `built in ...` dan manifest tersedia:

```powershell
Test-Path public/build/manifest.json
```

Hasilnya harus `True`. Pada sesi kita, build sudah berhasil; tidak perlu diulang hanya karena Composer sebelumnya gagal.

### Langkah 8 — Jalankan server

```powershell
php artisan serve --no-interaction
```

**Tanda berhasil:** terminal mencetak alamat server dan tetap menjalankan proses. Buka alamat yang dicetak terminal di browser. Gunakan alamat aktual tersebut, bukan menebak port yang aktif.

Jika perintah langsung kembali ke prompt tanpa alamat, periksa bagian 6. Jangan menganggap server sudah menyala.

## 4. Login dan mencoba web

Akun berikut tersedia setelah seeder berhasil:

| Peran | NIS/NIP | Password demo |
| --- | --- | --- |
| Pustakawan/admin | `admin` | `password` |
| Ahmad | `2024101` | `password` |
| Siti | `2024102` | `password` |
| Budi | `2024103` | `password` |

1. Buka alamat server dan login sebagai `admin`.
2. Pastikan dashboard, katalog, dan sampul buku tampil.
3. Logout, lalu login sebagai murid untuk melihat tampilan murid.
4. Untuk kios presensi, buka path `/kiosk` pada server yang sama.

Akun demo untuk latihan. Cara peminjaman, pengembalian, impor, dan laporan dijelaskan dalam [README](README.md).

## 5. Menutup dan menjalankan kembali

### Saat selesai atau akan mematikan laptop

1. Selesaikan transaksi yang sedang dikirim melalui browser.
2. Klik terminal yang menjalankan `php artisan serve`, lalu tekan **Ctrl+C**.
3. Jika memakai `npm run dev` di terminal lain, hentikan juga dengan **Ctrl+C**.
4. Simpan file yang diedit dengan **Ctrl+S**, lalu tutup editor dan matikan laptop secara normal.

Data yang sudah berhasil disimpan tetap ada di database SQLite. Menghentikan server tidak menghapus data. Isian form yang belum dikirim belum tersimpan.

### Saat membuka kembali pada hari berikutnya

```powershell
cd D:\perpus24\perpusdupat
php artisan serve --no-interaction
```

Buka alamat dari terminal. Jika kode, dependensi, dan konfigurasi tidak berubah, tidak perlu mengulang instalasi, pembuatan kunci, migrasi, seeder, atau build.

### Saat mengedit tampilan

Gunakan dua terminal:

- Terminal 1: `php artisan serve --no-interaction`.
- Terminal 2: `npm run dev`.

Untuk memakai hasil aset tanpa server Vite, hentikan `npm run dev`, lalu jalankan `npm run build`. Buka aplikasi melalui alamat server Laravel.

## 6. Masalah yang kita alami dan penyelesaiannya

### A. Perintah tidak dikenali

**Gejala:** `The term 'php' is not recognized` atau pesan serupa untuk Composer, Node.js, dan npm.

**Temuan awal:** aplikasi Herd sudah terpasang, tetapi PHP belum ditemukan di folder bin Herd dan PATH belum memuat Herd.

**Penanganan:** selesaikan pengaturan awal Herd beserta unduhan komponennya, lalu tutup dan buka kembali seluruh editor serta PowerShell. Periksa nomor versi satu per satu. Jika masih gagal, gunakan `Get-Command php` atau `Get-Command composer` untuk memeriksa apakah perintah ditemukan. Menjalankan sebagai administrator tidak dengan sendirinya memperbaiki PATH.

### B. Composer dan Artisan langsung berhenti tanpa pesan

**Gejala:** `composer install` atau `php artisan serve` kembali ke prompt tanpa proses instalasi atau alamat server.

**Temuan:** PHP 8.4.26 Herd keluar dengan kode `-1058471934` (`0xC0E90002`). Log Windows Code Integrity, Event ID 3077, mencatat pemblokiran `php84\php8.dll` oleh kebijakan penandatanganan. Masalah terjadi pada runtime PHP sebelum Laravel berjalan. Keluaran kosong pada komputer lain belum tentu memiliki penyebab yang sama.

Pemeriksaan yang digunakan:

```powershell
& "$env:USERPROFILE\.config\herd\bin\php84\php.exe" -v
$LASTEXITCODE
```

Baca kode keluar langsung setelah perintah yang bermasalah. Pengguna juga mengonfirmasi Smart App Control **On** melalui Windows Security → App & browser control → Smart App Control.

**Penanganan yang dipilih:** pertahankan Smart App Control, lalu pasang PHP 8.3 melalui menu PHP pada Herd. Jika perangkat dikelola organisasi, libatkan admin untuk kebijakan perangkat.

### C. PHP 8.3 memunculkan peringatan OPcache diblokir

**Gejala:**

```text
Failed loading Zend extension 'opcache'
php_opcache.dll (An Application Control policy has blocked this file)
```

Untuk membedakan masalah executable dan konfigurasi, kita menjalankan:

```powershell
& "$env:USERPROFILE\.config\herd\bin\php83\php.exe" -n -v
$LASTEXITCODE
```

**Hasil yang dikonfirmasi pengguna:** PHP 8.3.35 menampilkan versi dan kode keluar `0`. Opsi `-n` melewati konfigurasi PHP; gunakan untuk diagnosis saja, bukan untuk menjalankan aplikasi yang membutuhkan ekstensi database dan lainnya.

Pemeriksaan lanjutan `-m` dengan konfigurasi normal juga selesai dengan kode `0`. SQLite, OpenSSL, curl, mbstring, GD, dan ZIP berhasil dimuat; peringatan yang terlihat hanya OPcache.

**Langkah perbaikan yang diberikan untuk lingkungan lokal ini:**

1. Buka konfigurasi PHP 8.3 yang sudah ditemukan:

   ```powershell
   notepad "$env:USERPROFILE\.config\herd\bin\php83\php.ini"
   ```

2. Cari baris `zend_extension=opcache` dengan Ctrl+F, lalu tambahkan titik koma:

   ```ini
   ;zend_extension=opcache
   ```

3. Simpan. Ini menghentikan pemuatan OPcache yang opsional; tidak mengizinkan DLL yang diblokir dan tidak mematikan Smart App Control. Ekstensi lainnya tetap dipertahankan. OPcache tidak diperlukan untuk latihan lokal proyek ini.
4. Atur versi global Herd melalui PHP yang sudah dapat dijalankan:

   ```powershell
   & "$env:USERPROFILE\.config\herd\bin\php83\php.exe" "$env:USERPROFILE\.config\herd\bin\herd.phar" use 8.3
   ```

5. Periksa ulang:

   ```powershell
   php -v
   composer --version
   ```

   PHP harus menampilkan versi 8.3 dan Composer harus menampilkan versinya tanpa peringatan tersebut. Jika terminal mengarah ke versi lama, buka terminal baru dan periksa `Get-Command php`.

6. Kembali ke folder proyek, kemudian ulangi `composer install`.

Jika ekstensi lain ikut diblokir, jangan menonaktifkan semuanya. Catat nama file dan periksa kebutuhannya dahulu. Solusi di atas khusus untuk temuan kita, bukan jaminan bahwa semua versi PHP lolos semua kebijakan Windows.

Rujukan: [pemuatan OPcache](https://www.php.net/manual/en/opcache.installation.php), [versi PHP Herd](https://latest.herdphp.com/docs/windows/1/advanced-usage/php-versions), dan [FAQ Smart App Control](https://support.microsoft.com/en-us/windows/security/threat-malware-protection/smart-app-control-frequently-asked-questions). Microsoft mendokumentasikan batasan pengaktifan ulang Smart App Control; jangan menganggap mematikannya selalu dapat dibatalkan dengan satu klik.

### D. Build berhasil, tetapi web belum berjalan

Build menghasilkan aset CSS, JavaScript, dan font. Build tidak memasang dependensi PHP atau menyalakan server Laravel.

Screenshot kita menunjukkan `built in 3.55s`, tetapi `vendor/autoload.php` belum ada saat diperiksa. Langkah berikutnya adalah memperbaiki PHP, menyelesaikan `composer install`, lalu menyiapkan database dan server.

### E. Peringatan kuning fontaine dan PLUGIN_TIMINGS

Screenshot build memuat peringatan paket opsional `fontaine` untuk optimasi fallback font serta informasi waktu plugin. Build tetap menghasilkan manifest/aset dan mencetak pesan berhasil.

Pada tahap ini tidak perlu menambah paket hanya untuk menghilangkan peringatan. Periksa hasil build dan error sebenarnya, bukan warna teks saja.

### F. Salah membuka file konfigurasi

Screenshot editor menampilkan `.env.example`. Template itu memang memiliki `APP_KEY` kosong. Periksa `.env` yang sebenarnya sebelum menyimpulkan kunci belum dibuat. Jangan mengisi kunci rahasia ke template yang dilacak Git.

## 7. Pemeriksaan masalah lain

Ini panduan tambahan, bukan daftar masalah yang semuanya sudah dialami.

| Gejala | Tindakan |
| --- | --- |
| `Could not open input file: artisan` | Masuk ke folder proyek yang berisi `artisan` |
| `vendor/autoload.php` hilang | Pastikan PHP/Composer berfungsi, lalu selesaikan `composer install` |
| Composer melaporkan ekstensi kurang | Catat nama ekstensi; periksa `php --ini` dan `php -m` |
| Kunci aplikasi belum ditentukan | Pastikan `.env` ada; jalankan `key:generate` jika kuncinya kosong |
| `could not find driver` untuk SQLite | Periksa apakah `pdo_sqlite` tersedia dalam `php -m` |
| Tabel belum ada | Periksa koneksi `.env`, lalu jalankan migrasi |
| Vite manifest hilang | Pasang dependensi frontend jika belum ada, lalu `npm run build` |
| Sampul lokal tidak tampil | Periksa `storage:link` dan file di `storage/app/public/books` |
| Port server dipakai | Coba `php artisan serve --port=8001 --no-interaction`, buka alamat keluarannya |
| Akun demo gagal login | Periksa database yang digunakan dan apakah seeder pernah berhasil; jangan langsung mengulangnya |

## 8. Ringkasan penggunaan perintah

| Perintah | Kapan digunakan |
| --- | --- |
| `composer install` | Instalasi awal atau saat dependensi PHP dari lock berubah |
| `npm ci --ignore-scripts` | Instalasi awal atau pemasangan ulang dependensi frontend dari lock |
| `php artisan key:generate --no-interaction` | Instalasi baru dengan APP_KEY kosong |
| `php artisan migrate --no-interaction` | Instalasi awal atau saat ada migrasi baru |
| `php artisan db:seed --no-interaction` | Sekali untuk mengisi database demo baru |
| `php artisan storage:link --no-interaction` | Saat link penyimpanan publik belum ada |
| `npm run build` | Instalasi awal atau setelah aset tampilan berubah |
| `php artisan serve --no-interaction` | Setiap kali ingin menjalankan server lokal |
| Ctrl+C | Menghentikan server di terminal aktif |

## 9. Status verifikasi

- Screenshot membuktikan build frontend berhasil.
- Log Windows membuktikan pemblokiran PHP 8.4 oleh Code Integrity.
- Pengguna mengonfirmasi PHP 8.3.35 dengan `-n -v` berhasil, kode keluar `0`.
- Pemeriksaan modul PHP 8.3 normal berhasil dengan peringatan OPcache; ekstensi penting lainnya terdaftar.
- Pengguna melanjutkan tahap Composer dan penggunaan harian setelah menerima langkah perbaikan. Keluaran lengkap keberhasilan Composer, migrasi, dan halaman login tidak dilampirkan kembali. Dokumentasi ini tidak mengklaim pengujian end-to-end atau seluruh tes aplikasi sudah lulus.

Pembuatan dokumentasi ini tidak menjalankan ulang seeder, mengganti kunci, atau mengubah konfigurasi PHP/database. Setelah lingkungan siap, pengembang dapat menjalankan tes dengan `php artisan test --compact --no-interaction`.
