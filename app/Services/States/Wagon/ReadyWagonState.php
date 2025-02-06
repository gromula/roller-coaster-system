<?php

namespace App\Services\States\Wagon;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;
use App\Utils\RedisKeyHelper;

class ReadyWagonState implements StateWagonInterface
{
    public function handle(RedisService $redis, string $wagonId, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void
    {
        $redisClient = $redis->getClient();
        $coasterKey = RedisKeyHelper::coaster($coasterId);
        $wagonKey = RedisKeyHelper::wagon($coasterId, $wagonId);

        // Pobieramy liczbę dostępnych pracowników
        $staffAvailable = intval($redisClient->hget($coasterKey, "liczba_personelu"));
        $wagonsInTransit = count($redisClient->smembers(RedisKeyHelper::wagonInTransit($coasterId)));

        // Każdy wagon potrzebuje 2 pracowników + 1 dla kolejki
        $requiredStaff = ($wagonsInTransit + 1) * 2 + 1;

        if ($staffAvailable < $requiredStaff) {
            // Brakuje personelu – wrzucamy kolejkę do NeedsStaff
            $redisClient->sadd("coasters:{$coasterId}:wagons:needs_staff", $wagonId);
            $redisClient->hset($coasterKey, "state", "needs_staff");

            $logger->warning("🚧 Wagon {$wagonId} nie może ruszyć – brakuje personelu! (Dostępne: {$staffAvailable}, Wymagane: {$requiredStaff})");
            return;
        }

        // Wysyłamy wagon w trasę
        $nextRun = time() + (5 * 60); //TODO do wyrzucenia do env
        $redisClient->hset($wagonKey, "state", "in_transit");
        $redisClient->hset($wagonKey, "next_run", $nextRun);
        $redisClient->srem("coasters:{$coasterId}:wagons:ready", $wagonId);
        $redisClient->sadd("coasters:{$coasterId}:wagons:in_transit", $wagonId);

        $logger->info("🚃 Wagon {$wagonId} wyruszył w trasę!");
    }
}
