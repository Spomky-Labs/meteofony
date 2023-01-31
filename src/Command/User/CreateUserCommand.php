<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Uid\Ulid;
use Throwable;

#[AsCommand('app:user:create')]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = (string) $io->askQuestion(new Question('Nom d’utilisateur'));
        $email = (string) $io->askQuestion(new Question('Adresse email'));

        try {
            $hasher = $this->hasherFactory->getPasswordHasher(User::class);
            $password = $hasher->hash('SF-2023');
            $user = new User(Ulid::generate(), $email, $username, $password);
            $user->setPassword($password);
            $this->userRepository->save($user, true);
        } catch (Throwable $e) {
            $io->error(sprintf('Une erreur est survenue : %s', $e->getMessage()));
            return self::FAILURE;
        }

        $io->success('L’utilisateur a bien été créé');

        return self::SUCCESS;
    }
}
