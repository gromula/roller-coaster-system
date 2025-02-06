<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\RedisService;
use Psr\Log\LoggerInterface;

class MonitorCommand extends BaseCommand
{
    protected $group       = 'Monitoring';
    protected $name        = 'monitor:start';
    protected $description = '📊 Live monitoring kolejek i wagonów';

    public function run(array $params)
    {
        $redisService = new RedisService();
        $redisClient = $redisService->getClient();
        $logger = service('logger');

        CLI::write('📡 Live Monitoring started...', 'green');

        // Subskrypcja do zmian w kluczach `coasters:*`
        $redisClient->psubscribe(['__keyspace@2__:coasters:*', '__keyspace@2__:coasters:*:wagons:*'], function ($redis, $pattern, $channel, $message) use ($logger) {
            $logger->info("🔔 Redis Event: [$channel] - $message");

            // Jeśli status kolejek lub wagonów się zmienia, pobieramy nowy stan
            if ($message === 'set' || $message === 'hset') {
                $coasterId = str_replace(['__keyspace@2__:coasters:', ':wagons:*'], '', $channel);
                $this->updateMonitor($coasterId, $logger);
            }
        });
    }

    private function updateMonitor(string $coasterId, LoggerInterface $logger): void
    {
        $redisService = new RedisService();
        $redisClient = $redisService->getClient();

        $details = $redisClient->hgetall("coasters:$coasterId");
        $wagons = $redisClient->smembers("coasters:$coasterId:wagons:ready");
        $staffCount = count($redisClient->smembers("coasters:$coasterId:staff"));
        $totalWagons = count($redisClient->smembers("coasters:$coasterId:wagons:all"));

        $status = match ($details['state']) {
            'idle' => '✅ OK',
            'needs_staff' => "⚠ Brakuje {$details['missing_staff']} pracowników",
            'needs_wagons' => "⚠ Brakuje {$details['missing_wagons']} wagonów",
            'closed' => '🚫 Zamknięta',
            default => '❓ Nieznany stan',
        };

        $logger->info("
        [🎢 Kolejka: {$coasterId}]
        1. Godziny działania: {$details['godziny_od']} - {$details['godziny_do']}
        2. Liczba wagonów: " . count($wagons) . "/{$totalWagons}
        3. Dostępny personel: {$staffCount}/{$details['liczba_personelu']}
        4. Klienci dziennie: {$details['liczba_klientow']}
        5. Status: {$status}
        ");
    }
}
