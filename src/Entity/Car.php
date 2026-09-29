<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cars')]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VehicleCategory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private VehicleCategory $category;

    #[ORM\Column(length: 100)]
    private string $make;

    #[ORM\Column(length: 100)]
    private string $model;

    #[ORM\Column(length: 32, unique: true)]
    private string $registrationNumber;

    #[ORM\Column(type: 'smallint')]
    private int $modelYear;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $dailyRate;

    #[ORM\Column(length: 20)]
    private string $status;

    public function __construct(
        VehicleCategory $category,
        string $make,
        string $model,
        string $registrationNumber,
        int $modelYear,
        string $dailyRate,
        string $status = 'available',
    ) {
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->registrationNumber = $registrationNumber;
        $this->modelYear = $modelYear;
        $this->dailyRate = $dailyRate;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): VehicleCategory
    {
        return $this->category;
    }

    public function setCategory(VehicleCategory $category): void
    {
        $this->category = $category;
    }

    public function getMake(): string
    {
        return $this->make;
    }

    public function setMake(string $make): void
    {
        $this->make = $make;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getRegistrationNumber(): string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): void
    {
        $this->registrationNumber = $registrationNumber;
    }

    public function getModelYear(): int
    {
        return $this->modelYear;
    }

    public function setModelYear(int $modelYear): void
    {
        $this->modelYear = $modelYear;
    }

    public function getDailyRate(): string
    {
        return $this->dailyRate;
    }

    public function setDailyRate(string $dailyRate): void
    {
        $this->dailyRate = $dailyRate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
