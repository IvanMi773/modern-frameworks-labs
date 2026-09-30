<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\OneToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: true, unique: true, onDelete: 'RESTRICT')]
    private ?Client $client;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column]
    private string $password;

    /** @param list<string> $roles */
    public function __construct(string $email, string $password, array $roles = ['ROLE_CLIENT'], ?Client $client = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->setRoles($roles);
        $this->client = $client;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): void
    {
        $this->roles = array_values(array_unique($roles));
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function eraseCredentials(): void
    {
    }
}
