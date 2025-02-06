<?php

namespace App\Services\States\Wagon;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;
use App\Utils\RedisKeyHelper;

class BreakWagonState implements StateWagonInterface
{
    public function handle(RedisService $redis, string $wagonId, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void
    {
        $redisClient = $redis->getClient();
        
        $wagonKey = RedisKeyHelper::wagon($coasterId, $wagonId);

        $nextRun = intval($redisClient->hget($wagonKey, "next_run") ?? 0);
        if ($nextRun > time()) {
            $logger->warning("⏳ Wagon {$wagonId} nie może jeszcze ruszyć, czeka do " . date("H:i:s", $nextRun));
            return;
        }

        $redisClient->hset($wagonKey, "state", "ready");
        $redisClient->srem("coasters:{$coasterId}:wagons:break", $wagonId);
        $redisClient->sadd("coasters:{$coasterId}:wagons:ready", $wagonId);
        $redisClient->hset($wagonKey, "state", "ready");
        
        $logger->info("✅ Wagon {$wagonId} gotowy do jazdy!");
    }
}
