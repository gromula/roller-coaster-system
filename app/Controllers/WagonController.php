<?php

namespace App\Controllers;

use App\Services\WagonService;
use CodeIgniter\RESTful\ResourceController;
use App\DTO\WagonDTO;
use Exception;

class WagonController extends ResourceController
{
    private WagonService $wagonService;

    public function __construct()
    {
        $this->wagonService = service('wagonService');
    }

    /**
     * 📌 Dodaje nowy wagon do kolejki 🎢
     */
    public function createWagon($coasterId)
    {
        try {
            $data = $this->request->getJSON(true);
            $wagonDTO = new WagonDTO($coasterId, $data);
            $wagonId = $this->wagonService->addWagon($wagonDTO);

            return $this->respondCreated(["message" => "Wagon dodany!", "id" => $wagonId]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 📌 Usuwa wagon z kolejki 🎢
     */
    public function deleteWagon($coasterId, $wagonId)
    {
        try {
            $this->wagonService->removeWagon($coasterId, $wagonId);
            return $this->respondDeleted(["message" => "Wagon {$wagonId} usunięty!"]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}