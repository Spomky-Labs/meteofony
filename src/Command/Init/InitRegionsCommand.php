<?php

declare(strict_types=1);

namespace App\Command\Init;

use App\Entity\Region;
use App\Repository\RegionRepository;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use function count;
use function is_string;
use const JSON_THROW_ON_ERROR;

#[AsCommand(name: 'app:init:regions', description: 'Initialisation des régions',)]
final readonly class InitRegionsCommand
{
    public function __construct(
        private string $projectDirectory,
        private RegionRepository $regionRepository
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $data = file_get_contents($this->projectDirectory . '/data/regions.json');
        is_string($data) || throw new RuntimeException('Impossible de trouver les données concernant les régions.');
        /** @var array<array{id: int, code: string, name: string, slug: string}> $regions */
        $regions = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        $io->progressStart(count($regions));
        $counter = 0;
        foreach ($regions as $region) {
            $io->progressAdvance();
            if ($this->regionRepository->count([
                'id' => $region['id'],
            ]) !== 0) {
                continue;
            }
            $counter++;
            $object = new Region($region['id'], $region['code'], $region['name'], $region['slug']);

            $this->regionRepository->save($object);
            if ($counter > 1000) {
                $this->regionRepository->flush();
                $counter = 0;
            }
        }
        $io->progressFinish();
        $this->regionRepository->flush();
        return Command::SUCCESS;
    }
}
