<?php

namespace App\Controllers;

use App\Services\MonitoringService;
use CodeIgniter\Controller;

class MonitoringController extends Controller
{
    private MonitoringService $monitoringService;

    public function __construct()
    {
        $redis = new \Predis\Client([
            'host' => 'redis',
            'port' => 6379
        ]);
        $logger = service('logger');

        $this->monitoringService = new MonitoringService($redis, $logger);
    }

    public function status()
    {
        return $this->response->setJSON($this->monitoringService->getStatistics())->setStatusCode(200);
    }
}
