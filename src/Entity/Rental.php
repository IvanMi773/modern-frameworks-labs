<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rentals')]
class Rental
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Car::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Car $car;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Client $client;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $startsAt;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $endsAt;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $dailyRate;

    #[ORM\Column(length: 20)]
    private string $status;

    public function __construct(
        Car $car,
        Client $client,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        string $dailyRate,
        string $status = 'reserved',
    ) {
        $this->car = $car;
        $this->client = $client;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->dailyRate = $dailyRate;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
