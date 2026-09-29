<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'clients')]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100)]
    private string $lastName;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $phone;

    #[ORM\Column(length: 80, unique: true)]
    private string $driverLicenseNumber;

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $driverLicenseNumber,
        ?string $phone = null,
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->driverLicenseNumber = $driverLicenseNumber;
        $this->phone = $phone;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getDriverLicenseNumber(): string
    {
        return $this->driverLicenseNumber;
    }

    public function setDriverLicenseNumber(string $driverLicenseNumber): void
    {
        $this->driverLicenseNumber = $driverLicenseNumber;
    }
}
