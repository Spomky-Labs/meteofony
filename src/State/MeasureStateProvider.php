<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CityRepository;
use ArrayIterator;
use LogicException;
use function count;

class MeasureStateProvider implements ProviderInterface
{
    use FakeMeasureTrait;

    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly Pagination $pagination,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! $operation instanceof CollectionOperationInterface) {
            throw new LogicException('This provider only supports collection operations.');
        }
        $cityId = $uriVariables['id'] ?? null;
        if ($cityId === null) {
            return null;
        }
        $city = $this->cityRepository->findOneById($cityId);
        if ($city === null) {
            return null;
        }

        $measures = $this->getMeasures($city);
        return new TraversablePaginator(
            new ArrayIterator($measures),
            $this->pagination->getPage($context),
            $this->pagination->getLimit($operation, $context),
            count($measures)
        );
    }
}
