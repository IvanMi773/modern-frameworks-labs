<?php

namespace App\Dto;

final readonly class CarPayload
{
    public function __construct(
        public int $categoryId,
        public string $make,
        public string $model,
        public string $registrationNumber,
        public int $modelYear,
        public string $dailyRate,
        public string $status = 'available',
    ) {
    }
}
