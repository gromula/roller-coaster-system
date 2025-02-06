<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CoasterCommandService;
use App\Services\RedisService;

class Scheduler extends BaseCommand
{
    protected $group       = 'Schedulers';
    protected $name        = 'scheduler:start';
    protected $description = 'Schedulers starting.';

    public function run(array $params)
    {
        $redisService = new RedisService();
        $coasterCommandService = new CoasterCommandService($redisService);
        $logger = service('logger');

        CLI::write('🚀 Scheduler started!', 'green');

        try {
            while (true) {
                $coasterCommandService->processAll($logger);
                sleep(1); // Unikamy nadmiernego obciążenia CPU
            }
        } catch (\Exception $e) {
            CLI::write('❌ Error: ' . $e->getMessage(), 'red');
        }
    }
}
