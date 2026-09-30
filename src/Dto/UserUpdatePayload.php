<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserUpdatePayload
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public string $email,
        #[Assert\Choice(choices: ['ROLE_CLIENT', 'ROLE_MANAGER', 'ROLE_ADMIN'])]
        public string $role,
        #[Assert\Length(min: 12, max: 4096)]
        public ?string $password = null,
    ) {
    }
}
