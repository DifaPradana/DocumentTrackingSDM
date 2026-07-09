<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MaintenanceDown extends Command
{
    protected $signature = 'maintenance:down 
                            {--hours=0 : Estimasi durasi dalam jam}
                            {--minutes=0 : Estimasi durasi dalam menit}
                            {--secret= : Secret bypass agar tetap bisa akses}
                            {--refresh=30 : Interval auto-refresh browser (detik)}';

    protected $description = 'Aktifkan mode maintenance dengan estimasi durasi custom';

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $minutes = (int) $this->option('minutes');

        if ($hours === 0 && $minutes === 0) {
            $hours = 1; // default 1 jam kalau tidak diisi
        }

        $targetTime = Carbon::now()->addHours($hours)->addMinutes($minutes);

        // Simpan waktu target ke file, TANPA sentuh .env
        file_put_contents(
            storage_path('app/maintenance_until.txt'),
            $targetTime->toIso8601String()
        );

        $params = [
            '--render' => 'errors::503',
            '--refresh' => $this->option('refresh'),
        ];

        if ($secret = $this->option('secret')) {
            $params['--secret'] = $secret;
        }

        $this->call('down', $params);

        $this->info("✅ Maintenance mode aktif.");
        $this->info("⏰ Estimasi selesai: {$targetTime->translatedFormat('d M Y, H:i')} WIB");

        if ($secret) {
            $this->info("🔑 Bypass URL: " . config('app.url') . "/{$secret}");
        }
    }
}
