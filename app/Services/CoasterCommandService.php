<?php

namespace App\Services;

use App\Services\States\Coaster\ClosedCoasterState;
use App\Services\States\Coaster\IdleCoasterState;
use App\Services\States\Coaster\NeedsStaffCoasterState;
use App\Services\States\Coaster\NeedsWagonsCoasterState;
use App\Services\States\Wagon\BreakWagonState;
use App\Services\States\Wagon\InTransitWagonState;
use App\Services\States\Wagon\ReadyWagonState;
use App\Utils\RedisKeyHelper;
use Psr\Log\LoggerInterface;

class CoasterCommandService
{
    private RedisService $redisService;
    private array $coasterStates = [];
    private array $wagonStates = [];

    public function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;

        // 🎢 Stany kolejek
        $this->coasterStates = [
            'idle'        => new IdleCoasterState(),
            'closed'      => new ClosedCoasterState(),
            'needs_staff' => new NeedsStaffCoasterState(),
            'needs_wagons'=> new NeedsWagonsCoasterState()
        ];

        // 🚋 Stany wagonów
        $this->wagonStates = [
            'ready'      => new ReadyWagonState(),
            'in_transit'     => new InTransitWagonState(),
            'break'      => new BreakWagonState()
        ];
    }

    /**
     * 📌 Obsługuje pojedynczą kolejkę
     */
    public function processCoaster(string $coasterId, LoggerInterface $logger): void
    {
        $redis = $this->redisService->getClient();

        if ($redis->sismember("coasters:in_processing", $coasterId)) {
            return;
        }

        $redis->sadd("coasters:in_processing", $coasterId);
        $state = $redis->hget(RedisKeyHelper::coaster($coasterId), 'state') ?? 'idle';

        if (isset($this->coasterStates[$state])) {
            $this->coasterStates[$state]->handle($this->redisService, $coasterId, $this, $logger);
        } else {
            $logger->error("❌ Nieznany stan kolejki {$coasterId}: {$state}");
        }

        $redis->srem("coasters:in_processing", $coasterId);
    }

    /**
     * 📌 Obsługuje pojedynczy wagon
     */
    public function processWagon(string $coasterId, string $wagonId, LoggerInterface $logger): void
    {
        $redis = $this->redisService->getClient();

        $state = $redis->hget(RedisKeyHelper::wagon($coasterId, $wagonId), 'state') ?? 'ready';

        if (isset($this->wagonStates[$state])) {
            $this->wagonStates[$state]->handle($this->redisService, $wagonId, $coasterId, $this, $logger);
        } else {
            $logger->error("❌ Nieznany stan wagonu {$wagonId}: {$state}");
        }
    }

    /**
     * 📌 Obsługuje **wszystkie** kolejki i wagony
     */
    public function processAll(LoggerInterface $logger): void
    {
        $redis = $this->redisService->getClient();
        $coasters = $redis->smembers("coasters:All");

        if (empty($coasters)) {
            $logger->info("⚠ Brak kolejek do przetworzenia.");
            return;
        }

        foreach ($coasters as $coasterId) {
            $this->processCoaster($coasterId, $logger);

            // 🔄 Obsługa wagonów
            $wagonIds = $redis->smembers(RedisKeyHelper::coasterWagons($coasterId));
            
            foreach ($wagonIds as $wagonId) {
                $this->processWagon($coasterId, $wagonId, $logger);
            }
        }
    }
}
