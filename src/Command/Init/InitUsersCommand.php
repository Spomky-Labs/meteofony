<?php

declare(strict_types=1);

namespace App\Command\Init;

use App\Entity\User;
use App\Repository\UserRepository;
use function count;
use function is_string;
use const JSON_THROW_ON_ERROR;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[AsCommand(name: 'app:init:users', description: 'Initialisation des utilisateurs',)]
final class InitUsersCommand extends Command
{
    public function __construct(
        private readonly string $projectDirectory,
        private readonly UserRepository $userRepository,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $hasher = $this->hasherFactory->getPasswordHasher(User::class);

        $data = file_get_contents($this->projectDirectory . '/data/users.json');
        is_string($data) || throw new RuntimeException(
            'Impossible de trouver les données concernant les utilisateurs.'
        );
        /** @var array<array{username: string, email: string, password: string, name: string, roles: array<string>}> $users */
        $users = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        $io->progressStart(count($users));
        $counter = 0;
        foreach ($users as $user) {
            $io->progressAdvance();
            if ($this->userRepository->count([
                'email' => $user['email'],
            ]) !== 0) {
                continue;
            }
            $counter++;
            $object = new User($user['email'], $user['username'], $hasher->hash($user['password']));
            $object->setRoles($user['roles']);

            $this->userRepository->save($object);
            if ($counter > 100) {
                $this->userRepository->flush();
                $counter = 0;
            }
        }
        $io->progressFinish();
        $this->userRepository->flush();

        return Command::SUCCESS;
    }
}
