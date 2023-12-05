<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Stringable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Stringable, TwoFactorInterface
{
    /**
     * @var array<string>
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 200, type: 'string', nullable: true)]
    private ?string $emailCode = null;

    /**
     * @var Collection<int, AccessToken>|AccessToken[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AccessToken::class, orphanRemoval: true)]
    private Collection $accessTokens;

    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        #[ORM\Column]
        private readonly string $id,
        #[ORM\Column(length: 180, unique: true)]
        private string $email,
        #[ORM\Column(length: 200, unique: true)]
        private string $username,
        #[ORM\Column]
        private string $password
    ) {
        $this->accessTokens = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->username, $this->email);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @see UserInterface
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function addRole(string ...$roles): self
    {
        foreach ($roles as $role) {
            $this->roles[] = $role;
        }
        $this->roles = array_unique($this->roles);

        return $this;
    }

    public function removeRole(string ...$roles): self
    {
        foreach ($roles as $role) {
            $this->roles = array_filter($this->roles, static fn (string $item): bool => $item !== $role);
        }
        $this->roles = array_unique($this->roles);

        return $this;
    }

    /**
     * @return Collection<int, AccessToken>
     */
    public function getAccessTokens(): Collection
    {
        return $this->accessTokens;
    }

    public function isEmailAuthEnabled(): bool
    {
        return true;
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    public function getEmailAuthCode(): string|null
    {
        return $this->emailCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailCode = $authCode;
    }
}
