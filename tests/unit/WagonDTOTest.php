<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\DTO\WagonDTO;
use InvalidArgumentException;

class WagonDTOTest extends TestCase
{
    public function testValidWagonDTO(): void
    {
        $coasterId = 'test_coaster';
        $wagonData = [
            'ilosc_miejsc'    => 32,
            'predkosc_wagonu' => 1.2
        ];

        $wagonDTO = new WagonDTO($coasterId, $wagonData);

        $this->assertEquals($coasterId, $wagonDTO->coasterId);
        $this->assertEquals($wagonData['ilosc_miejsc'], $wagonDTO->iloscMiejsc);
        $this->assertEquals($wagonData['predkosc_wagonu'], $wagonDTO->predkoscWagonu);
    }

    public function testMissingRequiredFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Brak wymaganych pól: ilosc_miejsc, predkosc_wagonu.");

        $coasterId = 'test_coaster';
        $wagonData = []; // Brak wymaganych pól

        new WagonDTO($coasterId, $wagonData);
    }

    public function testInvalidSeatCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Liczba miejsc w wagonie musi być większa niż 0.");

        $coasterId = 'test_coaster';
        $wagonData = [
            'ilosc_miejsc'    => 0, // Niepoprawna liczba miejsc
            'predkosc_wagonu' => 1.2
        ];

        new WagonDTO($coasterId, $wagonData);
    }

    public function testInvalidSpeed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Prędkość wagonu musi być większa niż 0.");

        $coasterId = 'test_coaster';
        $wagonData = [
            'ilosc_miejsc'    => 32,
            'predkosc_wagonu' => 0 // Niepoprawna prędkość
        ];

        new WagonDTO($coasterId, $wagonData);
    }
}
