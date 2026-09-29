<?php

namespace App\Dto;

final readonly class ClientPayload
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $driverLicenseNumber,
        public ?string $phone = null,
    ) {
    }
}
