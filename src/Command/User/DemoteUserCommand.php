<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use function is_array;
use function sprintf;

#[AsCommand('app:user:demote')]
final readonly class DemoteUserCommand
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function __invoke(
        #[Argument(name: 'email', description: 'Email address of the user concerned')]
        string $email,
        #[Argument(name: 'roles', description: 'Roles to be removed')]
        array $roles,
        SymfonyStyle $io
    ): int {
        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);
        if ($user === null) {
            $io->error('User not found');
            return self::FAILURE;
        }
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
        $user->removeRole(...$roles);
        $this->userRepository->save($user, true);
        $io->success('The user now has the following roles');
        $io->listing($user->getRoles());
        return self::SUCCESS;
    }
}
