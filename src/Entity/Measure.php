<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\State\MeasureStateProvider;
use DateTimeImmutable;

#[ApiResource(
    uriTemplate: '/cities/{id}/measures',
    operations: [new GetCollection(provider: MeasureStateProvider::class)],
    uriVariables: [
        'id' => new Link(toProperty: 'city', fromClass: City::class),
    ],
)]
class Measure
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public readonly string $id,
        public DateTimeImmutable $date,
        public City $city,
        public float $temperature,
        public float $temperatureFelt,
        public int $humidity,
        public int $windDirection,
        public int $windSpeed,
        public int $precipitation,
    ) {
    }
}
