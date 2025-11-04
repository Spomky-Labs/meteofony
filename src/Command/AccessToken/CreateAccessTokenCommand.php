<?php

declare(strict_types=1);

namespace App\Command\AccessToken;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;

#[AsCommand('app:access_token:create')]
final readonly class CreateAccessTokenCommand
{
    public function __construct(
        private UserRepository $userRepository,
        private AccessTokenRepository $accessTokenRepository
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $users = $this->userRepository->findAll();
        $user = $io->askQuestion(new ChoiceQuestion('User', $users));
        if (! $user instanceof User) {
            $io->error('The user does not exist');
            return self::FAILURE;
        }
        $value = bin2hex(random_bytes(32));
        $accessToken = new AccessToken($value, $user);
        $this->accessTokenRepository->save($accessToken, true);
        $io->success(sprintf('The access token has been successfully created. Its value is: %s', $value));
        return self::SUCCESS;
    }
}
