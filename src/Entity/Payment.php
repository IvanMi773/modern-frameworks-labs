<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Rental::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Rental $rental;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(length: 30)]
    private string $method;

    #[ORM\Column(length: 20)]
    private string $status;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $paidAt;

    public function __construct(
        Rental $rental,
        string $amount,
        string $method,
        string $status = 'pending',
        ?\DateTimeImmutable $paidAt = null,
    ) {
        $this->rental = $rental;
        $this->amount = $amount;
        $this->method = $method;
        $this->status = $status;
        $this->paidAt = $paidAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRental(): Rental
    {
        return $this->rental;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }
}
