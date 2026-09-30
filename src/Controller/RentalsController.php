<?php

namespace App\Controller;

use App\Entity\Rental;
use App\Entity\User;
use App\Service\EntitySearchService;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RentalsController extends AbstractController
{
    #[Route('/rentals/search', name: 'rentals_search', methods: ['GET'])]
    public function search(Request $request, EntitySearchService $searchService): JsonResponse
    {
        $scope = null;

        if (!$this->isGranted('ROLE_MANAGER')) {
            $user = $this->getUser();
            $clientId = $user instanceof User ? $user->getClient()?->getId() : null;

            if ($clientId === null) {
                throw $this->createAccessDeniedException();
            }

            $scope = static function (QueryBuilder $queryBuilder) use ($clientId): void {
                $queryBuilder
                    ->andWhere('IDENTITY(e.client) = :ownerId')
                    ->setParameter('ownerId', $clientId);
            };
        }

        $result = $searchService->search(Rental::class, [
            'id' => ['expression' => 'e.id', 'type' => 'integer'],
            'carId' => ['expression' => 'IDENTITY(e.car)', 'type' => 'integer'],
            'clientId' => ['expression' => 'IDENTITY(e.client)', 'type' => 'integer'],
            'startsAt' => ['expression' => 'e.startsAt', 'type' => 'date'],
            'endsAt' => ['expression' => 'e.endsAt', 'type' => 'date'],
            'dailyRate' => ['expression' => 'e.dailyRate', 'type' => 'decimal'],
            'status' => ['expression' => 'e.status', 'type' => 'exact'],
        ], $request->query->all(), $scope);

        return $this->json([
            'items' => array_map(static fn (Rental $rental): array => [
                'id' => $rental->getId(),
                'carId' => $rental->getCar()->getId(),
                'clientId' => $rental->getClient()->getId(),
                'startsAt' => $rental->getStartsAt()->format('Y-m-d'),
                'endsAt' => $rental->getEndsAt()->format('Y-m-d'),
                'dailyRate' => $rental->getDailyRate(),
                'status' => $rental->getStatus(),
            ], $result['items']),
            'pagination' => $result['pagination'],
        ]);
    }
}
