<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ShowScannerTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:token {--regenerate : Re-generate and overwrite the scanner Sanctum token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tampilkan atau re-generate Sanctum Bearer Token untuk scanner mobile device node';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::where('nis_nip', 'SCANNER-DEVICE-01')->first();

        if (! $user) {
            $this->error("Pengguna 'scanner-device' (NIS/NIP: SCANNER-DEVICE-01) tidak ditemukan. Jalankan php artisan library:init.");

            return Command::FAILURE;
        }

        if ($this->option('regenerate') || $user->tokens()->count() === 0) {
            $user->tokens()->delete();
            $token = $user->createToken('mobile-scanner-token');
            $plainTextToken = $token->plainTextToken;

            $this->info('======================================================');
            $this->info('TOKEN SANCTUM BARU DIBUAT UNTUK MOBILE SCANNER DEVICE:');
            $this->line("<fg=yellow;options=bold>{$plainTextToken}</>");
            $this->info('======================================================');
            $this->info('Gunakan token di atas pada Header Mobile App:');
            $this->line("Authorization: Bearer {$plainTextToken}");
        } else {
            $this->info('User scanner-device aktif.');
            $this->warn('Sanctum token yang telah dibuat tidak dapat ditampilkan ulang dalam plaintext demi keamanan.');
            $this->info("Gunakan opsi '--regenerate' untuk membuat token baru:");
            $this->line('php artisan library:token --regenerate');
        }

        return Command::SUCCESS;
    }
}
