<?php

namespace App\Controller;

use App\Entity\VehicleCategory;
use App\Service\EntitySearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class VehicleCategoriesController extends AbstractController
{
    #[Route('/vehicle-categories', name: 'vehicle_categories_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $categories = $entityManager->getRepository(VehicleCategory::class)->findBy([], ['id' => 'ASC']);

        return $this->json(array_map(static fn (VehicleCategory $category): array => [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'description' => $category->getDescription(),
        ], $categories));
    }

    #[Route('/vehicle-categories/search', name: 'vehicle_categories_search', methods: ['GET'])]
    public function search(Request $request, EntitySearchService $searchService): JsonResponse
    {
        $result = $searchService->search(VehicleCategory::class, [
            'id' => ['expression' => 'e.id', 'type' => 'integer'],
            'name' => ['expression' => 'e.name', 'type' => 'string'],
            'description' => ['expression' => 'e.description', 'type' => 'string', 'nullable' => true],
        ], $request->query->all());

        return $this->json([
            'items' => array_map(static fn (VehicleCategory $category): array => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
            ], $result['items']),
            'pagination' => $result['pagination'],
        ]);
    }
}
