<?php

namespace App\Controllers;

use App\Services\CoasterService;
use App\DTO\CoasterDTO;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use InvalidArgumentException;
use App\DTO\CoasterUpdateDTO;

class CoasterController extends ResourceController
{
    protected $format = 'json';
    private CoasterService $coasterService;

    public function __construct()
    {
        $this->coasterService = service('coasterService');
    }

    /**
     * 📌 Pobiera listę wszystkich kolejek 🎢
     */
    public function index()
    {
        try {
            $coasters = $this->coasterService->getAllCoasters();
            return $this->respond($coasters);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * 📌 Dodaje nową kolejkę górską 🎢
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);
            $dto = new CoasterDTO($data);
            $coasterId = $this->coasterService->createCoaster($dto);

            return $this->respondCreated([
                "message" => "Kolejka dodana!",
                "id" => $coasterId
            ]);
        } catch (Exception $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }

    public function updateCoaster($coasterId)
    {
        try {
            $data = $this->request->getJSON(true);
            $updateDTO = new CoasterUpdateDTO($data);
            $this->coasterService->updateCoaster($coasterId, $updateDTO);

            return $this->respond(["message" => "Kolejka {$coasterId} została zaktualizowana!"], 200);
        } catch (InvalidArgumentException $e) {
            return $this->failValidationErrors($e->getMessage());
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
