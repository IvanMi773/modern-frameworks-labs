<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegistrationPayload
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $firstName,
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $lastName,
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public string $email,
        #[Assert\NotBlank]
        #[Assert\Length(max: 80)]
        public string $driverLicenseNumber,
        #[Assert\NotBlank]
        #[Assert\Length(min: 12, max: 4096)]
        public string $password,
        #[Assert\Length(max: 40)]
        public ?string $phone = null,
    ) {
    }
}
