<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AuthorController extends AbstractController
{
    #[Route('/authors', name: 'api_authors_index', methods: ['GET'])]
    public function index(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->findAll();

        $data = array_map(static fn($a) => [
            'id' => $a->getId(),
            'first_name' => $a->getFirstName(),
            'last_name' => $a->getLastName(),
        ], $authors);

        return $this->json($data);
    }

    #[Route('/authors/{id}', name: 'api_authors_show', methods: ['GET'])]
    public function show(string $id, AuthorRepository $authorRepository): Response
    {
        $authorId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($authorId === false) {
            return $this->json(['error' => 'Invalid author id'], Response::HTTP_NOT_FOUND);
        }

        $author = $authorRepository->find($authorId);

        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $author->getId(),
            'first_name' => $author->getFirstName(),
            'last_name' => $author->getLastName(),
        ]);
    }

    #[Route('/authors', name: 'api_authors_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $first_name = trim((string)($payload['first_name'] ?? ''));
        $last_name = trim((string)($payload['last_name'] ?? ''));

        if ($first_name === '') {
            return $this->json(['error' => 'Field "first_name" is required'], Response::HTTP_BAD_REQUEST);
        }

        if ($last_name === '') {
            return $this->json(['error' => 'Field "last_name" is required'], Response::HTTP_BAD_REQUEST);
        }

        $author = new Author();
        $author->setFirstName($first_name);
        $author->setLastName($last_name);
        $em->persist($author);
        $em->flush();

        return $this->json(
            [
                'id' => $author->getId(),
                'first_name' => $author->getFirstName(),
                'last_name' => $author->getLastName(),
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/authors/{id}', name: 'api_authors_update', methods: ['PUT'])]
    public function update(
        string $id,
        Request $request,
        AuthorRepository $authorRepository,
        EntityManagerInterface $em
    ): Response {
        $authorId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($authorId === false) {
            return $this->json(['error' => 'Invalid author id'], Response::HTTP_NOT_FOUND);
        }

        $author = $authorRepository->find($authorId);

        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $first_name = trim((string)($payload['first_name'] ?? ''));
        $last_name = trim((string)($payload['last_name'] ?? ''));

        if ($first_name === '') {
            return $this->json(['error' => 'Field "first_name" is required'], Response::HTTP_BAD_REQUEST);
        }

        if ($last_name === '') {
            return $this->json(['error' => 'Field "last_name" is required'], Response::HTTP_BAD_REQUEST);
        }

        $author->setFirstName($first_name);
        $author->setLastName($last_name);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/authors/{id}', name: 'api_authors_delete', methods: ['DELETE'])]
    public function delete(
        string $id,
        AuthorRepository $authorRepository,
        EntityManagerInterface $em
    ): Response {
        $authorId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($authorId === false) {
            return $this->json(['error' => 'Invalid author id'], Response::HTTP_NOT_FOUND);
        }

        $author = $authorRepository->find($authorId);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($author);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
