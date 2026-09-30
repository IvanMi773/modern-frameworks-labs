<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\User;
use App\Service\EntitySearchService;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentsController extends AbstractController
{
    #[Route('/payments/search', name: 'payments_search', methods: ['GET'])]
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
                    ->innerJoin('e.rental', 'owned_rental')
                    ->andWhere('IDENTITY(owned_rental.client) = :ownerId')
                    ->setParameter('ownerId', $clientId);
            };
        }

        $result = $searchService->search(Payment::class, [
            'id' => ['expression' => 'e.id', 'type' => 'integer'],
            'rentalId' => ['expression' => 'IDENTITY(e.rental)', 'type' => 'integer'],
            'amount' => ['expression' => 'e.amount', 'type' => 'decimal'],
            'method' => ['expression' => 'e.method', 'type' => 'string'],
            'status' => ['expression' => 'e.status', 'type' => 'exact'],
            'paidAt' => ['expression' => 'e.paidAt', 'type' => 'datetime', 'nullable' => true],
        ], $request->query->all(), $scope);

        return $this->json([
            'items' => array_map(static fn (Payment $payment): array => [
                'id' => $payment->getId(),
                'rentalId' => $payment->getRental()->getId(),
                'amount' => $payment->getAmount(),
                'method' => $payment->getMethod(),
                'status' => $payment->getStatus(),
                'paidAt' => $payment->getPaidAt()?->format(\DateTimeInterface::ATOM),
            ], $result['items']),
            'pagination' => $result['pagination'],
        ]);
    }
}
