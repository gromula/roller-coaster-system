<?php

namespace App\Services\States\Coaster;

use App\Services\RedisService;
use App\Services\CoasterCommandService;
use Psr\Log\LoggerInterface;

interface StateCoasterInterface
{
    public function handle(RedisService $redis, string $coasterId, CoasterCommandService $context, LoggerInterface $logger): void;
}
