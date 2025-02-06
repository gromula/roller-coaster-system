<?php

namespace App\Services\States\Coaster;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;
use App\Utils\RedisKeyHelper;

class NeedsStaffCoasterState implements StateCoasterInterface
{
    public function handle(RedisService $redis, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void
    {
        $redisClient = $redis->getClient();
        $staffCount = $redisClient->hget(RedisKeyHelper::coaster($coasterId), "liczba_personelu");
        $wagonCount = count($redisClient->smembers(RedisKeyHelper::coasterWagons($coasterId)));

        $requiredStaff = 1 + ($wagonCount * 2);

        if ($staffCount >= $requiredStaff) {
            $redisClient->hset(RedisKeyHelper::coaster($coasterId), "state", "idle");
            $logger->info("✅ Kolejka {$coasterId} ma wystarczający personel. Przenoszenie do 'idle'.");
        }
    }
}
