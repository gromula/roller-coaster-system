<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\CoasterService;
use App\Services\RedisService;
use App\DTO\CoasterDTO;
use App\Utils\RedisKeyHelper;
use App\DTO\CoasterUpdateDTO;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Predis\Client;
use Exception;

class CoasterServiceTest extends TestCase
{
    /** 
     * @var \PHPUnit\Framework\MockObject\MockObject&\App\Services\RedisService 
     */
    private RedisService|MockObject $redisService;
    /** 
     * @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Log\LoggerInterface 
     */
    private LoggerInterface|MockObject $logger;
    private CoasterService $coasterService;

    protected function setUp(): void
    {
        $this->redisService = new RedisService();
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->logger->expects($this->any())->method('info');
        $this->logger->expects($this->any())->method('warning');
        $this->logger->expects($this->any())->method('error');

        // Inicjalizujemy serwis
        $this->coasterService = new CoasterService($this->redisService, $this->logger);
    }
    protected function tearDown(): void
    {
        $this->redisService->getClient()->flushdb();
    }

    public function testCreateCoaster(): void
    {
        $data = [
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ];

        $coasterDTO = new CoasterDTO($data);
        $coasterId = $this->coasterService->createCoaster($coasterDTO);
        $result = $this->redisService->getClient()->hgetall(RedisKeyHelper::coaster($coasterId));

        $this->assertNotEmpty($result);
        $this->assertEquals('idle', $result['state']);
    }


    public function testGetCoaster(): void
    {
        $data = [
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ];
        $coasterDTO = new CoasterDTO($data);
        $coasterId = $this->coasterService->createCoaster($coasterDTO);

        $result = $this->coasterService->getCoaster($coasterId);
        $this->assertNotNull($result);
        $this->assertEquals($data['godziny_od'], $result['godziny_od']);
    }

    public function testGetAllCoasters(): void
    {
        $data1 = new CoasterDTO([
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ]);

        $data2 = new CoasterDTO([
            'godziny_od'       => '09:00',
            'godziny_do'         => '19:00',
            'liczba_personelu' => 12,
            'liczba_klientow'  => 50000,
            'dl_trasy'         => 1500,
        ]);

        $this->coasterService->createCoaster($data1);
        $this->coasterService->createCoaster($data2);

        $allCoasters = $this->coasterService->getAllCoasters();

        $this->assertNotEmpty($allCoasters);
        $this->assertCount(2, $allCoasters);
    }

    public function testDeleteCoaster(): void
    {
        $data = [
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ];
        $coasterDTO = new CoasterDTO($data);
        $coasterId = $this->coasterService->createCoaster($coasterDTO);

        $this->assertTrue($this->redisService->getClient()->exists(RedisKeyHelper::coaster($coasterId)) > 0);

        $this->coasterService->deleteCoaster($coasterId);
        $this->assertFalse($this->redisService->getClient()->exists(RedisKeyHelper::coaster($coasterId)) > 0);
    }

    public function testUpdateCoasterState(): void
    {
        $data = [
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ];
        $coasterDTO = new CoasterDTO($data);
        $coasterId = $this->coasterService->createCoaster($coasterDTO);

        $this->coasterService->updateCoasterState($coasterId, 'in_transit');
        $result = $this->redisService->getClient()->hget(RedisKeyHelper::coaster($coasterId), 'state');

        $this->assertEquals('in_transit', $result);
    }

    public function testUpdateCoasterStateInvalidState(): void
    {
        $this->expectException(Exception::class);
        $this->coasterService->updateCoasterState('non_existent', 'invalid_state');
    }

    public function testUpdateCoasterStateNonExistentCoaster(): void
    {
        $this->expectException(Exception::class);
        $this->coasterService->updateCoasterState('non_existent', 'idle');
    }

    public function testGetNonExistentCoaster(): void
    {
        $this->expectException(Exception::class);
        $this->coasterService->getCoaster('non_existent');
    }

    public function testDeleteNonExistentCoaster(): void
    {
        $this->expectException(Exception::class);
        $this->coasterService->deleteCoaster('non_existent');
    }

    public function testCreateCoasterWithInvalidTimes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CoasterDTO([
            'godziny_od'       => '18:00',
            'godziny_do'         => '08:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ]);
    }

    public function testCreateAndupdateCoaster(): void
    {
        $data = [
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ];
        $coasterDTO = new CoasterDTO($data);
        $coasterId = $this->coasterService->createCoaster($coasterDTO);


        $updateData = [
            'liczba_personelu' => '15'
        ];

        $coasterUpdateDTO = new CoasterUpdateDTO($updateData);
        $this->coasterService->updateCoaster($coasterId, $coasterUpdateDTO);

        $result = $this->redisService->getClient()->hget(RedisKeyHelper::coaster($coasterId), 'liczba_personelu');

        $this->assertEquals('15', $result);
    }

    public function testCreateAndupdateCoasterWithNonExistingCoaster(): void
    {

        $this->expectException(Exception::class);

        $updateData = [
            'liczba_personelu' => '15'
        ];

        $coasterUpdateDTO = new CoasterUpdateDTO($updateData);
        $this->coasterService->updateCoaster("invalidId", $coasterUpdateDTO);
    }


    public function testCreateCoasterWithRedisError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Nie udało się utworzyć kolejki.");
    
        $data = [
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ];
        $coasterDTO = new CoasterDTO($data);
        /** 
         * @var \PHPUnit\Framework\MockObject\MockObject&\App\Services\RedisService 
         */
        $mockRedisService = $this->createMock(RedisService::class);
        $mockRedisClient = $this->createMock(Client::class);

        $mockRedisClient->method($this->anything())->willThrowException(new Exception("Nie udało się utworzyć kolejki."));
        $mockRedisService->method('getClient')->willReturn($mockRedisClient);

        $coasterService = new CoasterService($mockRedisService, $this->logger);
        $coasterService->createCoaster($coasterDTO);
    }
    
    
}
