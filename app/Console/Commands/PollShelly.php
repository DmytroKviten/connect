<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\Reading;
use App\Support\PowerModeClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollShelly extends Command
{
    /**
     * Використання:
     *  php artisan shelly:poll
     *  php artisan shelly:poll --id=17
     *  php artisan shelly:poll --ip=192.168.1.50
     *  php artisan shelly:poll --sleep=250   # затримка 250 мс між пристроями
     */
    protected $signature   = 'shelly:poll {--id=} {--ip=} {--sleep=0}';
    protected $description = 'Опитати пристрої Shelly (Switch.GetStatus) і зберегти показники в readings';

    public function handle(): int
    {
        $query = Device::query()->whereNotNull('ip');

        if ($this->option('id')) {
            $query->where('id', (int)$this->option('id'));
        }
        if ($this->option('ip')) {
            $query->where('ip', $this->option('ip'));
        }

        /** @var \Illuminate\Support\Collection<int,\App\Models\Device> $devices */
        $devices = $query->get(['id','ip','mac','uid']);

        $ok = 0; $fail = 0; $total = $devices->count();
        $sleepMs = max(0, (int)$this->option('sleep'));

        if ($total === 0) {
            $this->info('Poll done: ok=0 fail=0 total=0');
            return self::SUCCESS;
        }

        foreach ($devices as $d) {

            // 🔥 докер + приватна адреса → пропускаємо, щоб не було вічних timeout
            if ($this->isRunningInDocker() && $this->isPrivateIp($d->ip)) {
                Log::notice("shelly:poll skip device #{$d->id} ({$d->ip}): private ip inside docker");
                continue;
            }

            try {
                // короткі таймаути, щоб не вішати php-fpm
                $resp = Http::timeout(1)->connectTimeout(0.5)
                    ->get("http://{$d->ip}/rpc/Switch.GetStatus", ['id' => 0]);

                if (!$resp->ok()) {
                    throw new \RuntimeException('HTTP '.$resp->status());
                }

                $st  = $resp->json();
                $now = now();

                // уніфіковано дістаємо значення з різних прошивок
                $power   = $this->extract($st, ['apower'], ['switch:0','apower']);
                $voltage = $this->extract($st, ['voltage'], ['switch:0','voltage']);
                $energy  = $this->extract($st, ['aenergy','total'], ['switch:0','aenergy','total']);

                $payload = [
                    'power_w'   => is_numeric($power)   ? (float)$power   : null,
                    'voltage_v' => is_numeric($voltage) ? (float)$voltage : null,
                    'energy_wh' => is_numeric($energy)  ? (int) round($energy) : null,
                    'taken_at'  => $now,
                ];
                $payload['mode'] = PowerModeClassifier::classify($payload['power_w']);

                // upsert в readings
                Reading::updateOrCreate(
                    ['device_id' => $d->id, 'taken_at' => $payload['taken_at']],
                    $payload
                );

                // оновлення IP, якщо Shelly сам віддав інший
                $newIp = $this->extract($st, ['wifi','sta_ip']);
                if (is_string($newIp) && filter_var($newIp, FILTER_VALIDATE_IP) && $newIp !== $d->ip) {
                    Device::whereKey($d->id)->update([
                        'ip'           => $newIp,
                        'last_seen_at' => $now,
                    ]);
                } else {
                    Device::whereKey($d->id)->update(['last_seen_at' => $now]);
                }

                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                Log::notice("shelly:poll fail for device #{$d->id} ({$d->ip}): ".$e->getMessage());
            }

            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }

        $this->info("Poll done: ok={$ok} fail={$fail} total={$total}");
        return self::SUCCESS;
    }

    /**
     * Безпечне витягування значення з масиву Shelly
     */
    private function extract(array $arr, array $pathA, array $pathB = null)
    {
        $get = function (array $a, array $path) {
            $cur = $a;
            foreach ($path as $k) {
                if (!is_array($cur) || !array_key_exists($k, $cur)) return null;
                $cur = $cur[$k];
            }
            return $cur;
        };

        $v = $get($arr, $pathA);
        if ($v === null && $pathB) {
            $v = $get($arr, $pathB);
        }
        return $v;
    }

    private function isRunningInDocker(): bool
    {
        return file_exists('/.dockerenv')
            || (is_readable('/proc/1/cgroup') && str_contains(file_get_contents('/proc/1/cgroup'), 'docker'));
    }

    private function isPrivateIp(string $ip): bool
    {
        if (str_starts_with($ip, '10.')) return true;
        if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) return true;
        if (str_starts_with($ip, '192.168.')) return true;
        return false;
    }
}
