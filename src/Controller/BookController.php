<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\DTO\Book\BookRequestDTO;
use App\DTO\Book\BookResponseDTO;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BookController extends AbstractController
{
    #[Route('/books', name: 'api_books_index', methods: ['GET'])]
    public function index(Request $request, BookRepository $bookRepository): Response
    {

        $authorId = $request->query->get('authorId');

        if ($authorId !== null) {
            $authorId = filter_var($authorId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($authorId === false) {
                return $this->json(['error' => 'Invalid authorId'], Response::HTTP_BAD_REQUEST);
            }
            $books = $bookRepository->findBy(['author' => $authorId]);
        } else {
            $books = $bookRepository->findAll();
        }

        $data = array_map(
            fn($b) => BookResponseDTO::fromEntity($b),
            $books
        );
        return $this->json($data);
    }

    #[Route('/books/{id}', name: 'api_books_show', methods: ['GET'])]
    public function show(string $id, BookRepository $bookRepository): Response
    {
        $bookId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($bookId === false) {
            return $this->json(['error' => 'Invalid book id'], Response::HTTP_NOT_FOUND);
        }

        $book = $bookRepository->find($bookId);

        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(BookResponseDTO::fromEntity($book));
    }

    #[Route('/books', name: 'api_books_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): Response {
        $dto = $serializer->deserialize(
            $request->getContent(),
            BookRequestDTO::class,
            'json'
        );

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string)$errors[0]->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $author = $em->getRepository(Author::class)->find($dto->authorId);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_BAD_REQUEST);
        }

        $book = new Book();
        $book->setTitle($dto->title);
        $book->setYear($dto->year);
        $book->setAuthor($author);
        $em->persist($book);
        $em->flush();

        return $this->json(BookResponseDTO::fromEntity($book), Response::HTTP_CREATED);
    }

    #[Route('/books/{id}', name: 'api_books_update', methods: ['PUT'])]
    public function update(
        string $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {
        $bookId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($bookId === false) {
            return $this->json(['error' => 'Invalid book id'], Response::HTTP_NOT_FOUND);
        }

        $book = $bookRepository->find($bookId);

        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $dto = $serializer->deserialize(
            $request->getContent(),
            BookRequestDTO::class,
            'json'
        );

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string)$errors[0]->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $author = $em->getRepository(Author::class)->find($dto->authorId);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_BAD_REQUEST);
        }

        $book->setTitle($dto->title);
        $book->setYear($dto->year);
        $book->setAuthor($author);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/books/{id}', name: 'api_books_delete', methods: ['DELETE'])]
    public function delete(
        string $id,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {
        $bookId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($bookId === false) {
            return $this->json(['error' => 'Invalid book id'], Response::HTTP_NOT_FOUND);
        }

        $book = $bookRepository->find($bookId);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($book);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
