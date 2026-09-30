<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Service\EntitySearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentsController extends AbstractController
{
    #[Route('/payments/search', name: 'payments_search', methods: ['GET'])]
    public function search(Request $request, EntitySearchService $searchService): JsonResponse
    {
        $result = $searchService->search(Payment::class, [
            'id' => ['expression' => 'e.id', 'type' => 'integer'],
            'rentalId' => ['expression' => 'IDENTITY(e.rental)', 'type' => 'integer'],
            'amount' => ['expression' => 'e.amount', 'type' => 'decimal'],
            'method' => ['expression' => 'e.method', 'type' => 'string'],
            'status' => ['expression' => 'e.status', 'type' => 'exact'],
            'paidAt' => ['expression' => 'e.paidAt', 'type' => 'datetime', 'nullable' => true],
        ], $request->query->all());

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
