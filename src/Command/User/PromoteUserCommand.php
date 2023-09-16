<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function is_array;

#[AsCommand('app:user:promote')]
final class PromoteUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email address of the user concerned');
        $this->addArgument('roles', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Roles to be added');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getArgument('email');
        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);
        if ($user === null) {
            $io->error('User not found');
            return self::FAILURE;
        }

        $roles = $input->getArgument('roles');
        if (! is_array($roles)) {
            $io->error('A list of roles must be chosen');
            return self::FAILURE;
        }
        $roles = array_map(static function (string $role): string {
            if (str_starts_with($role, 'ROLE_')) {
                return $role;
            }

            return sprintf('ROLE_%s', $role);
        }, $roles);

        $user->addRole(...$roles);
        $this->userRepository->save($user, true);

        $io->success('The user now has the following roles');
        $io->listing($user->getRoles());

        return self::SUCCESS;
    }
}
