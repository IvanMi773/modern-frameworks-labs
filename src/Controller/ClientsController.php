<?php

namespace App\Controller;

use App\Dto\ClientPayload;
use App\Entity\Client;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ClientsController extends AbstractController
{
    #[Route('/clients', name: 'clients_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $clients = $entityManager->getRepository(Client::class)->findBy([], ['id' => 'ASC']);

        return $this->json(array_map(fn (Client $client): array => $this->toArray($client), $clients));
    }

    #[Route('/clients/{id}', name: 'clients_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $client = $entityManager->find(Client::class, $id);

        if (!$client instanceof Client) {
            return $this->notFound();
        }

        return $this->json($this->toArray($client));
    }

    #[Route('/clients', name: 'clients_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] ClientPayload $payload,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $client = new Client(
            $payload->firstName,
            $payload->lastName,
            $payload->email,
            $payload->driverLicenseNumber,
            $payload->phone,
        );
        $entityManager->persist($client);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(
                ['error' => 'A client with that email or driver license number already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        return $this->json($this->toArray($client), Response::HTTP_CREATED, [
            'Location' => $this->generateUrl('clients_show', ['id' => $client->getId()]),
        ]);
    }

    #[Route('/clients/{id}', name: 'clients_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(
        int $id,
        #[MapRequestPayload] ClientPayload $payload,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $client = $entityManager->find(Client::class, $id);

        if (!$client instanceof Client) {
            return $this->notFound();
        }

        $client->setFirstName($payload->firstName);
        $client->setLastName($payload->lastName);
        $client->setEmail($payload->email);
        $client->setDriverLicenseNumber($payload->driverLicenseNumber);
        $client->setPhone($payload->phone);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(
                ['error' => 'A client with that email or driver license number already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        return $this->json($this->toArray($client));
    }

    #[Route('/clients/{id}', name: 'clients_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $client = $entityManager->find(Client::class, $id);

        if (!$client instanceof Client) {
            return $this->notFound();
        }

        $entityManager->remove($client);

        try {
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->json(
                ['error' => 'This client is referenced by a rental and cannot be deleted.'],
                Response::HTTP_CONFLICT,
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /** @return array<string, int|string|null> */
    private function toArray(Client $client): array
    {
        return [
            'id' => $client->getId(),
            'firstName' => $client->getFirstName(),
            'lastName' => $client->getLastName(),
            'email' => $client->getEmail(),
            'phone' => $client->getPhone(),
            'driverLicenseNumber' => $client->getDriverLicenseNumber(),
        ];
    }

    private function notFound(): JsonResponse
    {
        return $this->json(['error' => 'Client not found.'], Response::HTTP_NOT_FOUND);
    }
}
