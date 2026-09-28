<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:init {--fresh : Wipe database and re-migrate before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi sistem perpustakaan digital, generate akun default & mobile scanner token';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Menginisialisasi Sistem Perpustakaan Digital Dupat...');

        if ($this->option('fresh')) {
            $this->info('Menjalankan migrate:fresh...');
            Artisan::call('migrate:fresh', ['--force' => true], $this->output);
        }

        $this->info('Menjalankan DatabaseSeeder...');
        $this->call(DatabaseSeeder::class);

        $scannerUser = User::where('nis_nip', 'SCANNER-DEVICE-01')->first();
        if ($scannerUser) {
            $tokens = $scannerUser->tokens;
            $this->info('Status Token Sanctum: '.($tokens->count() > 0 ? 'Aktif ('.$tokens->count().' token)' : 'Tidak ada token aktif'));
        }

        $this->info('Inisialisasi selesai. Sistem siap digunakan pada jaringan LAN!');

        return Command::SUCCESS;
    }
}
