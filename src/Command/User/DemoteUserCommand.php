<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Repository\UserRepository;
use function is_array;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:user:demote')]
final class DemoteUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Adresse email de l’utilisateur concerné');
        $this->addArgument('roles', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Roles to be removed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getArgument('email');
        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);
        if ($user === null) {
            $io->error('L’utilisateur n’a pas été trouvé');
            return self::FAILURE;
        }

        $roles = $input->getArgument('roles');
        if (! is_array($roles)) {
            $io->error('Une liste de roles doit être choisie');
            return self::FAILURE;
        }
        $roles = array_map(static function (string $role): string {
            if (str_starts_with($role, 'ROLE_')) {
                return $role;
            }

            return sprintf('ROLE_%s', $role);
        }, $roles);

        $user->removeRole(...$roles);
        $this->userRepository->save($user, true);

        $io->success('L’utilisateur a maintenant les roles suivants');
        $io->listing($user->getRoles());

        return self::SUCCESS;
    }
}
