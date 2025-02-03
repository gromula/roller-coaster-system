<?php

namespace Config;

use App\Services\CoasterService;
use App\Services\RedisService;
use Psr\Log\LoggerInterface;
use App\Services\WagonService;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function coasterService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('coasterService');
        }

        return new CoasterService(
            service('redisService'),
            service('logger')
        );
    }

    public static function redisService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('redisService');
        }

        return new RedisService();
    }

    public static function wagonService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('wagonService');
        }

        return new WagonService(
            service('redisService'),
            service('logger')
        );
    }
}
