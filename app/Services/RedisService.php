<?php

namespace App\Services;

use CodeIgniter\CLI\CLI;
use Predis\Client;

class RedisService
{
    private Client $client;

    public function __construct()
    {
        

        $this->client = new Client([
            'scheme' => 'tcp',
            'host'   => getenv('REDIS_HOST') ?: 'redis',
            'port'   => getenv('REDIS_PORT') ?: 6379,
            'database' => match (getenv('CI_ENVIRONMENT')) {
                'development' => 0,
                'staging'     => 2,
                'production'  => 3,
                default => 0,
            }

            
        ]);

    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
