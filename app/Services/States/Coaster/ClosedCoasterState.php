<?php

namespace App\Services\States\Coaster;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;

class ClosedCoasterState implements StateCoasterInterface
{
    public function handle(RedisService $redis, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void
    {
        $logger->info("❌ Kolejka {$coasterId} jest zamknięta. Żadne operacje nie będą wykonywane.");

        $redis->getClient()->srem("coasters:idle", $coasterId);
        $redis->getClient()->sadd("coasters:closed", $coasterId);
    }
}
