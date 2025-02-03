<?php 

namespace App\Utils;

class RedisKeyHelper
{
    public static function coaster(string $id): string
    {
        return "coasters:{$id}";
    }

    public static function wagon(string $coasterId, string $wagonId): string
    {
        return "coasters:{$coasterId}:wagons:{$wagonId}";
    }

    public static function personnel(string $coasterId): string
    {
        return "coasters:{$coasterId}:personnel";
    }
}
