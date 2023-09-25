<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\City;
use App\Entity\Measure;
use App\Repository\CityRepository;
use DateTimeImmutable;
use LogicException;
use Symfony\Component\Uid\Ulid;

class MeasureStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly CityRepository $cityRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $cityId = $uriVariables['id'] ?? null;
        if ($cityId === null) {
            return null;
        }
        $city = $this->cityRepository->findOneById($cityId);
        if ($city === null) {
            return null;
        }
        $measures = $this->getMeasures($city);

        if (! $operation instanceof CollectionOperationInterface) {
            throw new LogicException('This provider only supports collection operations.');
        }

        return iterator_to_array($measures);
    }

    private function getMeasures(City $city): iterable
    {
        $now = new DateTimeImmutable();
        foreach (range(1, 100) as $i) {
            $date = $now->modify('-'.$i.' day');
            $temperature = random_int(-30, 30) + random_int(0, 100) / 100;
            yield new Measure(
                id: Ulid::generate(),
                date: $date,
                city: $city,
                temperature: $temperature,
                temperatureFelt: round($temperature + random_int(-5, 5) + random_int(0, 100) / 100, 2),
                humidity: random_int(0, 100),
                windDirection: random_int(0, 359),
                windSpeed: random_int(0, 100),
                precipitation: random_int(0, 100),
            );
        }
    }
}
