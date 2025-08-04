<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;

class RefreshDeviceIps extends Command
{
    protected $signature = 'devices:refresh-ips';

    protected $description = 'Оновлює IP розеток шляхом сканування локальної мережі';

    // Вкажи тут свій діапазон локальної мережі:
    protected $subnet = '192.168.33.';

    public function handle()
    {
        $this->info('Починаємо сканування мережі '.$this->subnet.'1-254');

        // Пінгуємо всі IP від 1 до 254
        for ($i = 1; $i <= 254; $i++) {
            $ip = $this->subnet . $i;
            $this->pingIp($ip);
        }

        $this->info('Отримуємо ARP таблицю...');

        $arpTable = $this->getArpTable();

        $this->info('ARP таблиця:');
        foreach ($arpTable as $ip => $mac) {
            $this->line("$ip => $mac");
        }

        foreach ($arpTable as $ip => $mac) {
            $macClean = strtoupper(str_replace([':', '-'], '', $mac));
            $this->info("Перевіряємо $ip з MAC $macClean");

            $device = Device::where('uid', $macClean)->first();

            if ($device && $device->ip !== $ip) {
                $this->info("Оновлення IP для пристрою {$device->name} ({$device->uid}): {$device->ip} → $ip");
                $device->ip = $ip;
                $device->save();
            }
        }

        $this->info('Сканування завершено.');

        return 0;
    }

    protected function pingIp(string $ip)
    {
        $os = strtolower(PHP_OS_FAMILY);

        if ($os === 'windows') {
            exec("ping -n 1 -w 100 $ip > NUL");
        } else {
            exec("ping -c 1 -W 1 $ip > /dev/null");
        }
    }

    protected function getArpTable(): array
    {
        $output = [];
        $result = [];

        $os = strtolower(PHP_OS_FAMILY);

        if ($os === 'windows') {
            exec('arp -a', $output);
            foreach ($output as $line) {
                if (preg_match('/(\d+\.\d+\.\d+\.\d+)\s+([-\w]{17})/', $line, $matches)) {
                    $ip = $matches[1];
                    $mac = $matches[2];
                    $result[$ip] = $mac;
                }
            }
        } else {
            exec('arp -n', $output);
            foreach ($output as $line) {
                if (preg_match('/(\d+\.\d+\.\d+\.\d+)\s+.*\s+([0-9a-f:]{17})/i', $line, $matches)) {
                    $ip = $matches[1];
                    $mac = $matches[2];
                    $result[$ip] = $mac;
                }
            }
        }

        return $result;
    }
}
