<?php

namespace App\DTO;

use InvalidArgumentException;

class CoasterUpdateDTO
{
    public ?int $liczbaPersonelu;
    public ?int $liczbaKlientow;
    public ?string $startTime;
    public ?string $endTime;

    public function __construct(array $data)
    {
        $allowedFields = ['liczba_personelu', 'liczba_klientow', 'godziny_od', 'godziny_do'];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedFields)) {
                throw new InvalidArgumentException("Pole {$key} nie może być zmienione.");
            }
        }

        if (isset($data['godziny_od']) && isset($data['godziny_do'])) {
            if (strtotime($data['godziny_od']) >= strtotime($data['godziny_do'])) {
                throw new InvalidArgumentException("Godzina zakończenia nie może być wcześniejsza niż godzina rozpoczęcia.");
            }
        }

        if (isset($data['liczba_personelu']) && $data['liczba_personelu'] < 1) {
            throw new InvalidArgumentException("Liczba personelu musi być większa niż 0.");
        }

        if (isset($data['liczba_klientow']) && $data['liczba_klientow'] < 1) {
            throw new InvalidArgumentException("Liczba klientów musi być większa niż 0.");
        }

        $this->liczbaPersonelu = $data['liczba_personelu'] ?? null;
        $this->liczbaKlientow = $data['liczba_klientow'] ?? null;
        $this->startTime = $data['godziny_od'] ?? null;
        $this->endTime = $data['godziny_do'] ?? null;
    }

    public function toArray(): array
    {
        return array_filter([
            'liczba_personelu' => $this->liczbaPersonelu,
            'liczba_klientow' => $this->liczbaKlientow,
            'godziny_od' => $this->startTime,
            'godziny_do' => $this->endTime,
        ], fn($value) => $value !== null);
    }
}
