<?php

namespace App\Controller;

use App\Dto\UserPayload;
use App\Dto\UserUpdatePayload;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class UsersController extends AbstractController
{
    #[Route('/users', name: 'users_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $users = $entityManager->getRepository(User::class)->findBy([], ['id' => 'ASC']);

        return $this->json(array_map($this->toArray(...), $users));
    }

    #[Route('/users/{id}', name: 'users_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->find(User::class, $id);

        return $user instanceof User
            ? $this->json($this->toArray($user))
            : $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/users', name: 'users_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] UserPayload $payload,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $client = null;

        if ($payload->role === 'ROLE_CLIENT') {
            if ($payload->clientId === null) {
                return $this->json(['error' => 'clientId is required for client accounts.'], Response::HTTP_BAD_REQUEST);
            }

            $client = $entityManager->find(Client::class, $payload->clientId);

            if (!$client instanceof Client) {
                return $this->json(['error' => 'Client not found.'], Response::HTTP_NOT_FOUND);
            }

            if ($entityManager->getRepository(User::class)->findOneBy(['client' => $client]) instanceof User) {
                return $this->json(['error' => 'That client already has an account.'], Response::HTTP_CONFLICT);
            }
        } elseif ($payload->clientId !== null) {
            return $this->json(['error' => 'Only client accounts may be linked to a client record.'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User(strtolower(trim($payload->email)), '', [$payload->role], $client);
        $user->setPassword($passwordHasher->hashPassword($user, $payload->password));
        $entityManager->persist($user);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'A user with that email already exists.'], Response::HTTP_CONFLICT);
        }

        return $this->json($this->toArray($user), Response::HTTP_CREATED, [
            'Location' => $this->generateUrl('users_show', ['id' => $user->getId()]),
        ]);
    }

    #[Route('/users/{id}', name: 'users_update', requirements: ['id' => '\\d+'], methods: ['PUT'])]
    public function update(
        int $id,
        #[MapRequestPayload] UserUpdatePayload $payload,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $user = $entityManager->find(User::class, $id);

        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($payload->role === 'ROLE_CLIENT' && $user->getClient() === null) {
            return $this->json(
                ['error' => 'Client accounts must be linked to a client record.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $user->setEmail(strtolower(trim($payload->email)));
        $user->setRoles([$payload->role]);

        if ($payload->password !== null && $payload->password !== '') {
            $user->setPassword($passwordHasher->hashPassword($user, $payload->password));
        }

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'A user with that email already exists.'], Response::HTTP_CONFLICT);
        }

        return $this->json($this->toArray($user));
    }

    #[Route('/users/{id}', name: 'users_delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->find(User::class, $id);

        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($user->getId() === $this->getUser()?->getId()) {
            return $this->json(['error' => 'You cannot delete the account you are using.'], Response::HTTP_CONFLICT);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /** @return array{id: int|null, email: string, roles: list<string>, clientId: int|null} */
    private function toArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'clientId' => $user->getClient()?->getId(),
        ];
    }
}
