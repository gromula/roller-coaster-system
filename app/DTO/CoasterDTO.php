<?php 
namespace App\DTO;

use InvalidArgumentException;

class CoasterDTO
{
    public ?string $id = null;
    public string $startTime;
    public string $endTime;
    public int $liczbaPersonelu;
    public int $liczbaKlientow;
    public int $dlTrasy;

    public function __construct(array $data)
    {
        if (!isset($data['godziny_od'], $data['godziny_do'], $data['liczba_personelu'], $data['liczba_klientow'], $data['dl_trasy'])) {
            throw new InvalidArgumentException("Brak wymaganych pól.");
        }

        if (strtotime($data['godziny_od']) >= strtotime($data['godziny_do'])) {
            throw new InvalidArgumentException("Czas rozpoczęcia nie może być po czasie zakończenia.");
        }

        if ($data['liczba_personelu'] < 0) {
            throw new InvalidArgumentException("Liczba personelu musi być większa lub równa 0.");
        }

        if ($data['liczba_klientow'] <= 0) {
            throw new InvalidArgumentException("Liczba klientów musi być większa niż 0.");
        }

        $this->startTime = $data['godziny_od'];
        $this->endTime = $data['godziny_do'];
        $this->liczbaPersonelu = $data['liczba_personelu'];
        $this->liczbaKlientow = $data['liczba_klientow'];
        $this->dlTrasy = $data['dl_trasy'];
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
