<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Predis\Client;

class HealthController extends Controller
{
    public function index()
    {
        try {
            $redis = new Client([
                'host' => 'redis',
                'port' => 6379
            ]);

            $redis->set('health_check_last_update', date("Y-m-d H:i:s"));
            $redis->set('health_check', 'OK');
            if ($redis->get('health_check') === 'OK') {
                $status['redis'] = 'Connected';
            }
        } catch (\Exception $e) {
            $status['redis'] = 'Error: ' . $e->getMessage();
        }

        return $this->response->setJSON($status);
    }
}
