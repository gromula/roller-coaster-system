<?php

namespace App\Services\States\Wagon;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;
use App\Utils\RedisKeyHelper;

class InTransitWagonState implements StateWagonInterface
{
    public function handle(RedisService $redisService, string $wagonId, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void
    {
        $redisClient = $redisService->getClient();
        $wagonKey = RedisKeyHelper::wagon($coasterId, $wagonId);

        // Pobieramy czas zakończenia przejazdu
        $nextRun = intval($redisClient->hget($wagonKey, "next_run") ?? 0);
        if ($nextRun > time()) {
            $logger->info("🚋 Wagon {$wagonId} nadal w trasie, przyjazd o " . date("H:i:s", $nextRun));
            return;
        }

        // 🚦 Wagon kończy trasę, przechodzi na `Break`
        $breakTime = 300; // 5 minut przerwy
        $nextRun = time() + $breakTime;
        $redisClient->hset($wagonKey, "next_run", $nextRun);
        $redisClient->hset($wagonKey, "state", "break");

        // Przenosimy wagon do `Break`
        $redisClient->srem("coasters:{$coasterId}:wagons:in_transit", $wagonId);
        $redisClient->sadd("coasters:{$coasterId}:wagons:break", $wagonId);

        $logger->info("🛑 Wagon {$wagonId} zakończył trasę i przechodzi na przerwę do " . date("H:i:s", $nextRun));
    }
}
