<?php

declare(strict_types=1);

namespace App\Command\Init;

use App\Entity\User;
use App\Repository\UserRepository;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Uid\Ulid;
use function count;
use function is_string;
use const JSON_THROW_ON_ERROR;

#[AsCommand(name: 'app:init:users', description: 'User initialization')]
final readonly class InitUsersCommand
{
    public function __construct(
        private string $projectDirectory,
        private UserRepository $userRepository,
        private PasswordHasherFactoryInterface $hasherFactory
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $hasher = $this->hasherFactory->getPasswordHasher(User::class);
        $data = file_get_contents($this->projectDirectory . '/data/users.json');
        is_string($data) || throw new RuntimeException('Unable to find user data.');
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
            $object = new User(Ulid::generate(), $user['email'], $user['username'], $hasher->hash($user['password']));
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
