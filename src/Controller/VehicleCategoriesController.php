<?php

namespace App\Controller;

use App\Entity\VehicleCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}
