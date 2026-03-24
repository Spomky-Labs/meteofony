<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Repository\ResetPasswordRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:user:pwd-clear')]
final readonly class ClearPasswordRequestCommand
{
    public function __construct(
        private ResetPasswordRequestRepository $repository,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $all = $this->repository->findAll();
        foreach ($all as $pr) {
            $this->repository->remove($pr, true);
        }
        return Command::SUCCESS;
    }
}
