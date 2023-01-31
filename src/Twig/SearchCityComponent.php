<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('search_cities')]
class SearchCityComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    public function __construct(
        private readonly CityRepository $cityRepository
    ) {
    }

    /**
     * @return array<array-key, City>
     */
    public function getCities(): array
    {
        if ($this->query === null || $this->query === '') {
            return [];
        }

        return $this->cityRepository->findByNameLike($this->query);
    }
}
