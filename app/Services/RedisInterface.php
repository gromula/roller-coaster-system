<?php

namespace App\Services;

use Predis\Client;

interface RedisInterface
{
    public function getClient(): Client;
}
