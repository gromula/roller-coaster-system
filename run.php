<?php

require 'vendor/autoload.php';

use App\Services\CoasterCommandService;
use App\Services\RedisService;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('coaster');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$redisService = new RedisService();
$coasterCommandService = new CoasterCommandService($redisService);

$coasterCommandService->processAll($logger);
