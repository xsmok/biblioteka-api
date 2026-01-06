<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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

        $data = array_map(static fn($b) => [
            'id' => $b->getId(),
            'title' => $b->getTitle(),
            'year' => $b->getYear(),
            'author' => [
                'id' => $b->getAuthor()->getId(),
                'first_name' => $b->getAuthor()->getFirstName(),
                'last_name' => $b->getAuthor()->getLastName(),
            ],
        ], $books);
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

        return $this->json([
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'year' => $book->getYear(),
            'author' => [
                'id' => $book->getAuthor()->getId(),
                'first_name' => $book->getAuthor()->getFirstName(),
                'last_name' => $book->getAuthor()->getLastName(),
            ],
        ]);
    }

    #[Route('/books', name: 'api_books_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $title = trim((string)($payload['title'] ?? ''));
        $year = trim((int)($payload['year'] ?? ''));
        $authorId = trim((int)($payload['authorId'] ?? ''));

        if ($title === '') {
            return $this->json(['error' => 'Field "title" is required'], Response::HTTP_BAD_REQUEST);
        }

        if ($year === '') {
            return $this->json(['error' => 'Field "year" is required'], Response::HTTP_BAD_REQUEST);
        }

        if ($authorId === '') {
            return $this->json(['error' => 'Field "authorId" is required'], Response::HTTP_BAD_REQUEST);
        }

        $author = $em->getRepository(Author::class)->find($authorId);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_BAD_REQUEST);
        }

        $book = new Book();
        $book->setTitle($title);
        $book->setYear($year);
        $book->setAuthor($author);
        $em->persist($book);
        $em->flush();

        return $this->json(
            [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'year' => $book->getYear(),
                'author' => [
                    'id' => $author->getId(),
                    'first_name' => $author->getFirstName(),
                    'last_name' => $author->getLastName(),
                ],
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/books/{id}', name: 'api_books_update', methods: ['PUT'])]
    public function update(
        string $id,
        Request $request,
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

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $title = trim((string)($payload['title'] ?? ''));
        $year = trim((int)($payload['year'] ?? ''));
        $authorId = trim((int)($payload['authorId'] ?? ''));

        if ($title === '') {
            return $this->json(['error' => 'Field "title" is required'], Response::HTTP_BAD_REQUEST);
        }

        if ($year === '') {
            return $this->json(['error' => 'Field "year" is required'], Response::HTTP_BAD_REQUEST);
        }

        if ($year < 0) {
            return $this->json(['error' => 'Field "year" must be a positive integer'], Response::HTTP_BAD_REQUEST);
        }

        if ($authorId === '') {
            return $this->json(['error' => 'Field "authorId" is required'], Response::HTTP_BAD_REQUEST);
        }

        $author = $em->getRepository(Author::class)->find($authorId);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_BAD_REQUEST);
        }

        $book->setTitle($title);
        $book->setYear($year);
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
