<?php

namespace App\DTO\Author;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AuthorRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Field "first_name" is required')]
        public string $first_name,

        #[Assert\NotBlank(message: 'Field "last_name" is required')]
        public string $last_name,
    ) {}
}
