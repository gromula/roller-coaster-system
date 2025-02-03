<?php

namespace App\DTO;

use InvalidArgumentException;

class WagonDTO
{
    public ?string $id = null;
    public string $coasterId;
    public int $iloscMiejsc;
    public float $predkoscWagonu;

    public function __construct(string $coasterId, array $data)
    {
        if (!isset($data['ilosc_miejsc'], $data['predkosc_wagonu'])) {
            throw new InvalidArgumentException("Brak wymaganych pól: ilosc_miejsc, predkosc_wagonu.");
        }

        if ($data['ilosc_miejsc'] <= 0) {
            throw new InvalidArgumentException("Liczba miejsc w wagonie musi być większa niż 0.");
        }

        if ($data['predkosc_wagonu'] <= 0) {
            throw new InvalidArgumentException("Prędkość wagonu musi być większa niż 0.");
        }

        $this->coasterId = $coasterId;
        $this->iloscMiejsc = $data['ilosc_miejsc'];
        $this->predkoscWagonu = $data['predkosc_wagonu'];
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
