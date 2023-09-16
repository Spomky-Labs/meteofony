<?php

declare(strict_types=1);

namespace App\Command\Init;

use App\Entity\Department;
use App\Repository\DepartmentRepository;
use App\Repository\RegionRepository;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function count;
use function is_string;
use const JSON_THROW_ON_ERROR;

#[AsCommand(name: 'app:init:departments', description: 'Initialisation des départements',)]
final class InitDeparmentsCommand extends Command
{
    public function __construct(
        private readonly string $projectDirectory,
        private readonly DepartmentRepository $departmentRepository,
        private readonly RegionRepository $regionRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $data = file_get_contents($this->projectDirectory . '/data/departments.json');
        is_string($data) || throw new RuntimeException(
            'Impossible de trouver les données concernant les départements.'
        );
        /** @var array<array{id: int, region_code: string, code: string, name: string, slug: string}> $departments */
        $departments = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        $io->progressStart(count($departments));
        $counter = 0;
        foreach ($departments as $department) {
            $io->progressAdvance();
            if ($this->departmentRepository->count([
                'id' => $department['id'],
            ]) !== 0) {
                continue;
            }
            $counter++;
            $region = $this->regionRepository->findOneBy([
                'code' => $department['region_code'],
            ]);
            if ($region === null) {
                $io->error(
                    sprintf(
                        'Impossible de trouver la région avec le code "%s" pour le département "%s ("%s")',
                        $department['region_code'],
                        $department['name'],
                        $department['code']
                    )
                );
                continue;
            }

            $object = new Department(
                $department['id'],
                $region,
                $department['code'],
                $department['name'],
                $department['slug']
            );

            $this->departmentRepository->save($object);
            if ($counter > 1000) {
                $this->departmentRepository->flush();
                $counter = 0;
            }
        }
        $io->progressFinish();
        $this->departmentRepository->flush();

        return Command::SUCCESS;
    }
}
