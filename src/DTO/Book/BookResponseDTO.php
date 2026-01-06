<?php

namespace App\DTO\Book;

use App\DTO\Author\AuthorResponseDTO;
use App\Entity\Book;

final readonly class BookResponseDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public int $year,
        public AuthorResponseDTO $author,
    ) {}

    public static function fromEntity(Book $book): self
    {
        return new self(
            id: $book->getId(),
            title: $book->getTitle(),
            year: $book->getYear(),
            author: AuthorResponseDTO::fromEntity($book->getAuthor()),
        );
    }
}
