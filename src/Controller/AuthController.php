<?php

namespace App\Controller;

use App\Dto\RegistrationPayload;
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

final class AuthController extends AbstractController
{
    #[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegistrationPayload $payload,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $email = strtolower(trim($payload->email));
        $client = new Client(
            trim($payload->firstName),
            trim($payload->lastName),
            $email,
            trim($payload->driverLicenseNumber),
            $payload->phone,
        );
        $user = new User($email, '', ['ROLE_CLIENT'], $client);
        $user->setPassword($passwordHasher->hashPassword($user, $payload->password));

        $entityManager->persist($client);
        $entityManager->persist($user);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(
                ['error' => 'An account with that email or driver license number already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => 'ROLE_CLIENT',
            'clientId' => $client->getId(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('The login request is handled by the security firewall.');
    }
}
