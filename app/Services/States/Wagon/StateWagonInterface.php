<?php

namespace App\Services\States\Wagon;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;

interface StateWagonInterface
{
    public function handle(RedisService $redis, string $wagonId, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void;
}
