<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Device;

class RefreshShellyIp extends Command
{
    protected $signature = 'shelly:refresh-ip';
    protected $description = 'Шукає Shelly пристрої у локальній мережі та оновлює їх IP у базі';

    public function handle()
    {
        // Отримуємо IP з останньої активної сесії користувача
        $lastSessionIp = DB::table('sessions')
            ->whereNotNull('ip_address')
            ->orderByDesc('last_activity')
            ->value('ip_address');

        if (!$lastSessionIp) {
            $this->error('❌ Не вдалося отримати IP користувача з сесій.');
            return;
        }

        $this->info("🔍 IP останнього користувача: $lastSessionIp");

        // Визначаємо підмережу, наприклад: 192.168.1.
        $subnet = implode('.', array_slice(explode('.', $lastSessionIp), 0, 3)) . '.';
        $this->info("🌐 Скануємо мережу {$subnet}1–254...");

        for ($i = 1; $i <= 254; $i++) {
            $ip = $subnet . $i;

            try {
                $res = Http::timeout(1)
                    ->get("http://$ip/rpc/Sys.GetStatus");

                if (!$res->ok()) continue;

                $json = $res->json();

                if (!isset($json['device']['id'])) continue;

                $uid = $json['device']['id'];

                $dev = Device::where('uid', $uid)->first();

                if (!$dev) {
                    $this->warn("⚠️  Пристрій з uid $uid не знайдено у БД.");
                    continue;
                }

                $dev->update(['ip' => $ip]);
                $this->info("✅ Оновлено IP для {$dev->name} → $ip");

            } catch (\Throwable $e) {
                // Тихо ігноруємо IP, які не відповіли
            }
        }

        $this->info('✅ Сканування завершено.');
    }
}
