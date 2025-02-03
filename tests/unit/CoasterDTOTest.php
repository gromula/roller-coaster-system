<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\DTO\CoasterDTO;
use InvalidArgumentException;

class CoasterDTOTest extends TestCase
{
    public function testValidCoasterDTO(): void
    {
        $data = [
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 16,
            'liczba_klientow'  => 60000,
            'dl_trasy'         => 1800,
        ];

        $dto = new CoasterDTO($data);

        $this->assertEquals($data['godziny_od'], $dto->startTime);
        $this->assertEquals($data['godziny_do'], $dto->endTime);
        $this->assertEquals($data['liczba_personelu'], $dto->liczbaPersonelu);
        $this->assertEquals($data['liczba_klientow'], $dto->liczbaKlientow);
        $this->assertEquals($data['dl_trasy'], $dto->dlTrasy);
    }

    public function testMissingRequiredFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Brak wymaganych pól.");

        new CoasterDTO([
            'godziny_od' => '08:00',
            'godziny_do'   => '18:00',
            // Brakuje kluczowych pól jak liczba_personelu, liczba_klientow, dl_trasy
        ]);
    }

    public function testInvalidTimeRange(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Czas rozpoczęcia nie może być po czasie zakończenia.");

        new CoasterDTO([
            'godziny_od'       => '18:00', // ❌ Błędna godzina startowa
            'godziny_do'         => '08:00',
            'liczba_personelu' => 10,
            'liczba_klientow'  => 5000,
            'dl_trasy'         => 1500,
        ]);
    }

    public function testInvalidPersonnelCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Liczba personelu musi być większa lub równa 0.");

        new CoasterDTO([
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => -1, // ❌ Błędna liczba personelu
            'liczba_klientow'  => 6000,
            'dl_trasy'         => 1800,
        ]);
    }

    public function testInvalidClientCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Liczba klientów musi być większa niż 0.");

        new CoasterDTO([
            'godziny_od'       => '08:00',
            'godziny_do'         => '18:00',
            'liczba_personelu' => 10,
            'liczba_klientow'  => 0, // ❌ Błędna liczba klientów
            'dl_trasy'         => 1800,
        ]);
    }

}
