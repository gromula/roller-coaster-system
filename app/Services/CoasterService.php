<?php

namespace App\Services;

use App\DTO\CoasterDTO;
use App\Utils\RedisKeyHelper;
use App\DTO\CoasterUpdateDTO;
use Exception;
use Psr\Log\LoggerInterface;

class CoasterService
{
    private RedisService $redisService;
    private LoggerInterface $logger;

    public function __construct(RedisService $redisService, LoggerInterface $logger)
    {
        $this->redisService = $redisService;
        $this->logger = $logger;
    }

    public function getAllCoasters(): array
    {
        $redis = $this->redisService->getClient();
        $coasterIds = $redis->smembers("coasters:All");
        $result = [];

        $result = [];
        foreach ($coasterIds as $coasterId) {
            $details = $redis->hgetall(RedisKeyHelper::coaster($coasterId));
            if (!empty($details)) {
                $result[$coasterId] = $details;
            }
        }
        return $result;
    }

    public function createCoaster(CoasterDTO $coaster): string
    {
        $redis = $this->redisService->getClient();

        $coasterId = uniqid('coaster_', true);
        $coaster->setId($coasterId);

        $redisKey = RedisKeyHelper::coaster($coaster->id);

        try {
            $redis->hmset($redisKey, [
                'state' => 'idle',
                'godziny_od' => $coaster->startTime,
                'godziny_do' => $coaster->endTime,
                'next_run' => time(),
                'liczba_personelu' => $coaster->liczbaPersonelu,
                'liczba_klientow' => $coaster->liczbaKlientow,
                'dl_trasy' => $coaster->dlTrasy
            ]);

            $redis->sadd("coasters:Idle", $coasterId);
            $redis->sadd("coasters:All", $coasterId);
            
            $this->logger->info("🎢 Kolejka dodana: {$redisKey}");
            return $coaster->id;
        } catch (Exception $e) {
            $this->logger->error("🚨 Błąd przy dodawaniu kolejki {$redisKey}: " . $e->getMessage());
            throw new Exception("Nie udało się utworzyć kolejki.");
        }
    }


    public function getCoaster(string $coasterId): array
    {
        $redis = $this->redisService->getClient();
        $coaster = $redis->hgetall(RedisKeyHelper::coaster($coasterId));

        if (!$coaster) {
            throw new Exception("Kolejka o ID {$coasterId} nie istnieje.");
        }

        return $coaster;
    }


    //TODO do usunięcie, zrealzowana na potrzeby testów
    public function updateCoasterState(string $coasterId, string $coasterState): void
    {
        $redis = $this->redisService->getClient();

        if (!in_array($coasterState, ['idle', 'in_transit', 'break', 'inactive'])) {
            throw new Exception("Niepoprawny stan!");
        }

        $coasterKey = RedisKeyHelper::coaster($coasterId);
        if (!$redis->exists($coasterKey)) {
            throw new Exception("Kolejka o ID {$coasterId} nie istnieje.");
        }

        $currentState = $redis->hget($coasterKey, 'state');

        if ($currentState) {
            $redis->srem("coasters:$currentState", $coasterId);
        }
        $redis->sadd("coasters:$coasterState", $coasterId);

        $redis->hset($coasterKey, 'state', $coasterState);
        $this->logger->info("🔄 Zmieniono status kolejki {$coasterId} na {$coasterState}");
    }


    public function deleteCoaster(string $coasterId): void
    {
        $redis = $this->redisService->getClient();
        $coasterKey = RedisKeyHelper::coaster($coasterId);

        if (!$redis->exists($coasterKey)) {
            throw new Exception("Kolejka o ID {$coasterId} nie istnieje.");
        }

        foreach (['Idle', 'In_Transit', 'Break', 'Inactive'] as $state) {
            $redis->srem("coasters:$state", $coasterId);
        }

        $redis->del($coasterKey);
        $redis->srem("coasters:All", $coasterId);
        $this->logger->info("🗑️ Kolejka {$coasterId} usunięta.");
    }

public function updateCoaster(string $coasterId, CoasterUpdateDTO $updateDTO): void
{
    $redis = $this->redisService->getClient();
    $coasterKey = RedisKeyHelper::coaster($coasterId);

    if (!$redis->exists($coasterKey)) {
        throw new Exception("Kolejka o ID {$coasterId} nie istnieje.");
    }

    $updateData = $updateDTO->toArray();
    foreach ($updateData as $field => $value) {
        $redis->hset($coasterKey, $field, $value);
    }

    $state = $redis->hget($coasterKey, 'state');
    if ($state === 'blocked') {
        $redis->srem("coasters:blocked", $coasterId);
        $redis->sadd("coasters:idle", $coasterId);
    }

    $this->logger->info("✅ Kolejka {$coasterId} zaktualizowana.", ['updated_data' => $updateData]);
}

}
