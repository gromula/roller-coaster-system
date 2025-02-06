<?php

namespace App\Services\States\Coaster;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;
use App\Utils\RedisKeyHelper;

class NeedsWagonsCoasterState implements StateCoasterInterface
{
    public function handle(RedisService $redis, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void
    {
        $redisClient = $redis->getClient();
        $wagonCount = count($redisClient->smembers(RedisKeyHelper::coasterWagons($coasterId)));

        if ($wagonCount > 0) {
            $redisClient->hset(RedisKeyHelper::coaster($coasterId), "state", "idle");
            $logger->info("✅ Kolejka {$coasterId} ma wagony. Przenoszenie do 'idle'.");
        } else{
            $logger->info("❌ Kolejka {$coasterId} nie ma wagonów. Oczekiwanie na dostawę.");
        }
    }
}
