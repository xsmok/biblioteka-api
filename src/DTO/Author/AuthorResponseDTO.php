<?php

namespace App\DTO\Author;

use App\Entity\Author;

final readonly class AuthorResponseDTO
{
    public function __construct(
        public int $id,
        public string $first_name,
        public string $last_name,
    ) {}

    public static function fromEntity(Author $author): self
    {
        return new self(
            id: $author->getId(),
            first_name: $author->getFirstName(),
            last_name: $author->getLastName(),
        );
    }
}
