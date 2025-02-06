<?php

namespace App\Services\States\Coaster;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;
use App\Utils\RedisKeyHelper;

class IdleCoasterState implements StateCoasterInterface
{
    public function handle(RedisService $redis, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void
    {
        $redisClient = $redis->getClient();
        $logger->info("🟢 Kolejka {$coasterId} czeka na start.");

        $wagonCount = count($redisClient->smembers(RedisKeyHelper::coasterWagons($coasterId)));
        $staffCount = $redisClient->hget(RedisKeyHelper::coaster($coasterId), "liczba_personelu");

        // Liczymy wymagany personel
        $requiredStaff = 1 + ($wagonCount * 2);
        if ($staffCount < $requiredStaff) {
            $redisClient->hset(RedisKeyHelper::coaster($coasterId), "state", "needs_staff");
            $logger->warning("🚨 Kolejka {$coasterId} nie startuje – brak personelu. Potrzebne: {$requiredStaff}, dostępne: {$staffCount}.");
            return;
        }

        if ($wagonCount < 1) {
            $redisClient->hset(RedisKeyHelper::coaster($coasterId), "state", "needs_wagons");
            $logger->warning("🚨 Kolejka {$coasterId} nie startuje – brak wagonów.");
            return;
        }
    }
}
