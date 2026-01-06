<?php

namespace App\DTO\Book;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class BookRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Field "title" is required')]
        public string $title,

        #[Assert\NotNull(message: 'Field "year" is required')]
        #[Assert\Positive(message: 'Field "year" must be positive')]
        public int $year,

        #[Assert\NotNull(message: 'Field "authorId" is required')]
        #[Assert\Positive]
        public int $authorId,
    ) {}
}
