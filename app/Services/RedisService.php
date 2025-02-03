<?php

namespace App\Services;

use Predis\Client;

class RedisService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'host' => 'redis',
            'port' => 6379,
            'database' => 2,
        ]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
