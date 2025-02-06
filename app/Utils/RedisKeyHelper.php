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

    public static function coasterWagons(string $coasterId): string
    {
        return "coasters:{$coasterId}:wagons";
    }

    public static function wagonBreak(string $coasterId): string
    {
        return "coasters:{$coasterId}:wagons:break";
    }

    public static function wagonInTransit(string $coasterId): string
    {
        return "coasters:{$coasterId}:wagons:in_transit";
    }
}
