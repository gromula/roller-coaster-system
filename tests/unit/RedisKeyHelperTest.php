<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Utils\RedisKeyHelper;

class RedisKeyHelperTest extends TestCase
{
    public function testCoasterKeyGeneration(): void
    {
        $coasterId = 'test123';
        $expectedKey = "coasters:test123";

        $this->assertEquals($expectedKey, RedisKeyHelper::coaster($coasterId));
    }

    public function testWagonKeyGeneration(): void
    {
        $coasterId = 'test123';
        $wagonId = 'wagonA';
        $expectedKey = "coasters:test123:wagons:wagonA";

        $this->assertEquals($expectedKey, RedisKeyHelper::wagon($coasterId, $wagonId));
    }

    public function testPersonnelKeyGeneration(): void
    {
        $coasterId = 'test123';
        $expectedKey = "coasters:test123:personnel";

        $this->assertEquals($expectedKey, RedisKeyHelper::personnel($coasterId));
    }
}
