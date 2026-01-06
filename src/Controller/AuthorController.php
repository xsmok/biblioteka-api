<?php

namespace App\Controller;

use App\Entity\Author;
use App\DTO\Author\AuthorRequestDTO;
use App\Repository\AuthorRepository;
use App\DTO\Author\AuthorResponseDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AuthorController extends AbstractController
{
    #[Route('/authors', name: 'api_authors_index', methods: ['GET'])]
    public function index(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->findAll();

        $data = array_map(
            fn($b) => AuthorResponseDTO::fromEntity($b),
            $authors
        );
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

        return $this->json(AuthorResponseDTO::fromEntity($author));
    }

    #[Route('/authors', name: 'api_authors_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): Response {
        $dto = $serializer->deserialize(
            $request->getContent(),
            AuthorRequestDTO::class,
            'json'
        );

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string)$errors[0]->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $author = new Author();
        $author->setFirstName($dto->first_name);
        $author->setLastName($dto->last_name);
        $em->persist($author);
        $em->flush();

        return $this->json(AuthorResponseDTO::fromEntity($author), Response::HTTP_CREATED);
    }

    #[Route('/authors/{id}', name: 'api_authors_update', methods: ['PUT'])]
    public function update(
        string $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AuthorRepository $authorRepository,
        EntityManagerInterface $em
    ): Response {
        $authorId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($authorId === false) {
            return $this->json(['error' => 'Invalid author id'], Response::HTTP_NOT_FOUND);
        }

        $dto = $serializer->deserialize(
            $request->getContent(),
            AuthorRequestDTO::class,
            'json'
        );

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string)$errors[0]->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $author = $authorRepository->find($authorId);

        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $author->setFirstName($dto->first_name);
        $author->setLastName($dto->last_name);
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
