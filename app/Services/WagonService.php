<?php

namespace App\Services;

use App\DTO\WagonDTO;
use App\Utils\RedisKeyHelper;
use Exception;
use Psr\Log\LoggerInterface;

class WagonService
{
    private RedisService $redisService;
    private LoggerInterface $logger;

    public function __construct(RedisService $redisService, LoggerInterface $logger)
    {
        $this->redisService = $redisService;
        $this->logger = $logger;
    }

    public function addWagon(WagonDTO $wagonDTO): string
    {
        $redis = $this->redisService->getClient();
        
        
        $wagonId = uniqid('wagon_', true);
        $wagonDTO->setId($wagonId);

        $wagonKey = RedisKeyHelper::wagon($wagonDTO->coasterId, $wagonId);

        try {
            $redis->hmset($wagonKey, [
                'ilosc_miejsc' => $wagonDTO->iloscMiejsc,
                'predkosc_wagonu' => $wagonDTO->predkoscWagonu
            ]);

            $redis->sadd("coasters:wagons:{$wagonDTO->coasterId}", $wagonId);

            $this->logger->info("🚃 Wagon dodany: {$wagonKey}");
            return $wagonId;
        } catch (Exception $e) {
            $this->logger->error("🚨 Błąd przy dodawaniu wagonu {$wagonKey}: " . $e->getMessage());
            throw new Exception("Nie udało się dodać wagonu.");
        }
    }

    public function removeWagon(string $coasterId, string $wagonId): void
    {
        $redis = $this->redisService->getClient();
        $wagonKey = RedisKeyHelper::wagon($coasterId, $wagonId);
    
        if (!$redis->exists($wagonKey)) {
            throw new Exception("Wagon o ID {$wagonId} nie istnieje w kolejce {$coasterId}.");
        }
    
        $redis->srem("coasters:wagons:$coasterId", $wagonId);
        $redis->del($wagonKey);
        
        $this->logger->info("🗑️ Wagon {$wagonId} usunięty z kolejki {$coasterId}.");
    }
    
}
