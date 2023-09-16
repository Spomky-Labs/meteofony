<?php

declare(strict_types=1);

namespace App\Command\Init;

use App\Entity\City;
use App\Repository\CityRepository;
use App\Repository\DepartmentRepository;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function count;
use function is_string;
use const JSON_THROW_ON_ERROR;

#[AsCommand(name: 'app:init:cities', description: 'Initialisation des villes',)]
final class InitCitiesCommand extends Command
{
    public function __construct(
        private readonly string $projectDirectory,
        private readonly CityRepository $cityRepository,
        private readonly DepartmentRepository $departmentRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $data = file_get_contents($this->projectDirectory . '/data/cities.json');
        is_string($data) || throw new RuntimeException('Impossible de trouver les données concernant les villes.');
        /** @var array<array{id: int, department_code: string, insee_code: string, zip_code: string, name: string, slug: string, gps_lat: float, gps_lng: float}> $cities */
        $cities = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        $io->progressStart(count($cities));
        $counter = 0;
        foreach ($cities as $city) {
            $io->progressAdvance();
            if ($this->cityRepository->count([
                'id' => $city['id'],
            ]) !== 0) {
                continue;
            }
            $counter++;
            $department = $this->departmentRepository->findOneBy([
                'code' => $city['department_code'],
            ]);
            if ($department === null) {
                $io->error(
                    sprintf(
                        'Impossible de trouver le département avec le code "%s" pour la ville "%s ("%s")',
                        $city['department_code'],
                        $city['name'],
                        $city['insee_code']
                    )
                );
                continue;
            }

            $object = new City(
                $city['id'],
                $department,
                $city['insee_code'],
                $city['zip_code'],
                $city['name'],
                $city['slug'],
                $city['gps_lat'],
                $city['gps_lng'],
            );

            $this->cityRepository->save($object);
            if ($counter > 1000) {
                $this->cityRepository->flush();
                $counter = 0;
            }
        }
        $io->progressFinish();
        $this->cityRepository->flush();

        return Command::SUCCESS;
    }
}
