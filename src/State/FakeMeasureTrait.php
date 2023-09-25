<?php

declare(strict_types=1);

namespace App\State;

use App\ApiResource\Measure;
use App\Entity\City;
use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;

trait FakeMeasureTrait
{
    /**
     * @return array<Measure>
     */
    private function getMeasures(City $city): array
    {
        $measures = [];
        $now = new DateTimeImmutable();
        $temperature = random_int(-30, 30) + random_int(0, 100) / 100;
        $windSpeed = random_int(0, 100);
        foreach (range(1, 100) as $i) {
            $date = $now->modify('-' . $i . ' day');
            $temperature = +$temperature + random_int(-5, 5);
            $windSpeed = +$windSpeed + random_int(-5, 5);
            if ($windSpeed < 0) {
                $windSpeed *= -1;
            }
            $measures[] = new Measure(
                id: Ulid::generate(),
                date: $date,
                city: $city,
                temperature: $temperature,
                temperatureFelt: round($temperature + random_int(-5, 5) + random_int(0, 100) / 100, 2),
                humidity: random_int(0, 100),
                windDirection: random_int(0, 359),
                windSpeed: $windSpeed,
                precipitation: random_int(0, 100),
            );
        }

        return $measures;
    }
}
