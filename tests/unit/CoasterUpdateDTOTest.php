<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\DTO\CoasterUpdateDTO;
use InvalidArgumentException;

class CoasterUpdateDTOTest extends TestCase
{
    public function testValidData(): void
    {
        $data = [
            'liczba_personelu' => 10,
            'liczba_klientow' => 500,
            'godziny_od' => '08:00',
            'godziny_do' => '16:00'
        ];

        $dto = new CoasterUpdateDTO($data);
        $this->assertEquals($data, $dto->toArray());
    }

    public function testInvalidPersonnelCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Liczba personelu musi być większa niż 0.");

        new CoasterUpdateDTO(['liczba_personelu' => 0]);
    }

    public function testInvalidClientCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Liczba klientów musi być większa niż 0.");

        new CoasterUpdateDTO(['liczba_klientow' => 0]);
    }

    public function testInvalidTime(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Godzina zakończenia nie może być wcześniejsza niż godzina rozpoczęcia.");

        new CoasterUpdateDTO(['godziny_od' => '18:00', 'godziny_do' => '08:00']);
    }
    public function testInvalidArgumentPersonelCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Pole dl_trasy nie może być zmienione.");

        new CoasterUpdateDTO([
            'dl_trasy'         => 1800,
        ]);
    }
}
