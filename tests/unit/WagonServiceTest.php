<?php

namespace Tests\Unit;

use App\DTO\CoasterDTO;
use PHPUnit\Framework\TestCase;
use App\Services\WagonService;
use App\Services\RedisService;
use App\DTO\WagonDTO;
use App\Utils\RedisKeyHelper;
use Predis\Client;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Exception;

class WagonServiceTest extends TestCase
{
    /** 
     * @var \PHPUnit\Framework\MockObject\MockObject&\App\Services\RedisService 
     */
    private RedisService|MockObject $redisService;

    /** 
     * @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Log\LoggerInterface 
     */
    private LoggerInterface|MockObject $logger;

    private WagonService $wagonService;

    protected function setUp(): void
    {
        $this->redisService = new RedisService();
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->logger->expects($this->any())->method('info');
        $this->logger->expects($this->any())->method('warning');

        $this->wagonService = new WagonService($this->redisService, $this->logger);
    }

    protected function tearDown(): void
    {
        $this->redisService->getClient()->flushdb();
    }

    public function testAddWagon(): void
    {
        $coasterId = 'test_coaster';
        $wagonData = [
            'ilosc_miejsc'    => 32,
            'predkosc_wagonu' => 1.2
        ];

        $wagoDTO = new WagonDTO($coasterId, $wagonData);

        $wagonId = $this->wagonService->addWagon($wagoDTO);
        $storedWagon = $this->redisService->getClient()->hgetall(RedisKeyHelper::wagon($coasterId, $wagonId));

        $this->assertNotEmpty($wagonId, "ID wagonu nie powinno być puste.");
        $this->assertNotEmpty($storedWagon, "Wagon powinien zostać zapisany w Redis.");
        $this->assertEquals($wagonData['ilosc_miejsc'], $storedWagon['ilosc_miejsc']);
        $this->assertEquals($wagonData['predkosc_wagonu'], $storedWagon['predkosc_wagonu']);
    }

    public function testRemoveWagon(): void
    {
        $coasterId = 'test_coaster';
        $wagonData = [
            'ilosc_miejsc'    => 32,
            'predkosc_wagonu' => 1.2
        ];

        $wagoDTO = new WagonDTO($coasterId, $wagonData);

        $wagonId = $this->wagonService->addWagon($wagoDTO);
        $existsBefore = $this->redisService->getClient()->exists(RedisKeyHelper::wagon($coasterId, $wagonId));
        $this->assertTrue($existsBefore > 0, "Wagon powinien istnieć przed usunięciem.");

        $this->wagonService->removeWagon($coasterId, $wagonId);
        $existsAfter = $this->redisService->getClient()->exists(RedisKeyHelper::wagon($coasterId, $wagonId));
        $this->assertEquals(0, $existsAfter, "Wagon powinien zostać usunięty.");
    }


    public function testRemoveNonExistingWagon(): void
    {
        $coasterId = 'test_coaster';
        $wagonId = 'non_existing_wagon';

        $this->expectExceptionMessage("Wagon o ID {$wagonId} nie istnieje w kolejce {$coasterId}.");
        $this->wagonService->removeWagon($coasterId, $wagonId);
    }

    public function testCreateWagonWithRedisError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Nie udało się dodać wagonu.");
    
        $data = [
            'ilosc_miejsc'    => 32,
            'predkosc_wagonu' => 1.2
        ];
        $wagonDTO = new WagonDTO('test_coaster', $data);

        /** 
         * @var \PHPUnit\Framework\MockObject\MockObject&\App\Services\RedisService 
         */
        $mockRedisService = $this->createMock(RedisService::class);
        $mockRedisClient = $this->createMock(Client::class);

        $mockRedisClient->method($this->anything())->willThrowException(new Exception("Nie udało się dodać wagonu."));
        $mockRedisService->method('getClient')->willReturn($mockRedisClient);

        $wagonService = new WagonService($mockRedisService, $this->logger);
        $wagonService->addWagon($wagonDTO);
    }
}
