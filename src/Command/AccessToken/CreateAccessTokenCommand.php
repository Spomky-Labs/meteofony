<?php

declare(strict_types=1);

namespace App\Command\AccessToken;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;

#[AsCommand('app:access_token:create')]
final class CreateAccessTokenCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AccessTokenRepository $accessTokenRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

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
