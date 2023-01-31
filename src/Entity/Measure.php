<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;

class Measure
{
    private float $temperature;

    private float $temperatureFelt;

    private int $humidity;

    private int $windDirection;

    private int $windSpeed;

    private int $precipitation;

    public function __construct(
        private readonly int $id,
        private DateTimeImmutable $date,
        private City $city
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getHumidity(): int
    {
        return $this->humidity;
    }

    public function setHumidity(int $humidity): self
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function getTemperatureFelt(): float
    {
        return $this->temperatureFelt;
    }

    public function setTemperatureFelt(float $temperatureFelt): self
    {
        $this->temperatureFelt = $temperatureFelt;

        return $this;
    }

    public function getWindDirection(): int
    {
        return $this->windDirection;
    }

    public function setWindDirection(int $windDirection): self
    {
        $this->windDirection = $windDirection;

        return $this;
    }

    public function getWindSpeed(): int
    {
        return $this->windSpeed;
    }

    public function setWindSpeed(int $windSpeed): self
    {
        $this->windSpeed = $windSpeed;

        return $this;
    }

    public function getPrecipitation(): int
    {
        return $this->precipitation;
    }

    public function setPrecipitation(int $precipitation): self
    {
        $this->precipitation = $precipitation;

        return $this;
    }
}
