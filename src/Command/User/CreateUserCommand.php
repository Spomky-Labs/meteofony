<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Uid\Ulid;
use Throwable;
use function sprintf;

#[AsCommand('app:user:create')]
final readonly class CreateUserCommand
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasherFactoryInterface $hasherFactory
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $username = (string) $io->askQuestion(new Question('Username'));
        $email = (string) $io->askQuestion(new Question('Email address'));
        try {
            $hasher = $this->hasherFactory->getPasswordHasher(User::class);
            $password = $hasher->hash('SF-2023');
            $user = new User(Ulid::generate(), $email, $username, $password);
            $user->setPassword($password);
            $this->userRepository->save($user, true);
        } catch (Throwable $e) {
            $io->error(sprintf('An error has occurred : %s', $e->getMessage()));
            return self::FAILURE;
        }
        $io->success('The user has been successfully created');
        return self::SUCCESS;
    }
}
