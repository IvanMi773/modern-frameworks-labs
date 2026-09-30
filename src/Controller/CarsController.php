<?php

namespace App\Controller;

use App\Dto\CarPayload;
use App\Entity\Car;
use App\Entity\VehicleCategory;
use App\Service\EntitySearchService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CarsController extends AbstractController
{
    #[Route('/cars', name: 'cars_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $cars = $entityManager->getRepository(Car::class)->findBy([], ['id' => 'ASC']);

        return $this->json(array_map(fn (Car $car): array => $this->toArray($car), $cars));
    }

    #[Route('/cars/search', name: 'cars_search', methods: ['GET'])]
    public function search(Request $request, EntitySearchService $searchService): JsonResponse
    {
        $result = $searchService->search(Car::class, [
            'id' => ['expression' => 'e.id', 'type' => 'integer'],
            'categoryId' => ['expression' => 'IDENTITY(e.category)', 'type' => 'integer'],
            'make' => ['expression' => 'e.make', 'type' => 'string'],
            'model' => ['expression' => 'e.model', 'type' => 'string'],
            'registrationNumber' => ['expression' => 'e.registrationNumber', 'type' => 'string'],
            'modelYear' => ['expression' => 'e.modelYear', 'type' => 'integer'],
            'dailyRate' => ['expression' => 'e.dailyRate', 'type' => 'decimal'],
            'status' => ['expression' => 'e.status', 'type' => 'exact'],
        ], $request->query->all());

        return $this->json([
            'items' => array_map(fn (Car $car): array => $this->toArray($car), $result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    #[Route('/cars/{id}', name: 'cars_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $car = $entityManager->find(Car::class, $id);

        if (!$car instanceof Car) {
            return $this->notFound();
        }

        return $this->json($this->toArray($car));
    }

    #[Route('/cars', name: 'cars_create', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function create(
        #[MapRequestPayload] CarPayload $payload,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $category = $entityManager->find(VehicleCategory::class, $payload->categoryId);

        if (!$category instanceof VehicleCategory) {
            return $this->json(['error' => 'Vehicle category not found.'], Response::HTTP_NOT_FOUND);
        }

        $car = new Car(
            $category,
            $payload->make,
            $payload->model,
            $payload->registrationNumber,
            $payload->modelYear,
            $payload->dailyRate,
            $payload->status,
        );
        $entityManager->persist($car);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'A car with that registration number already exists.'], Response::HTTP_CONFLICT);
        }

        return $this->json($this->toArray($car), Response::HTTP_CREATED, [
            'Location' => $this->generateUrl('cars_show', ['id' => $car->getId()]),
        ]);
    }

    #[Route('/cars/{id}', name: 'cars_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_MANAGER')]
    public function update(
        int $id,
        #[MapRequestPayload] CarPayload $payload,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $car = $entityManager->find(Car::class, $id);

        if (!$car instanceof Car) {
            return $this->notFound();
        }

        $category = $entityManager->find(VehicleCategory::class, $payload->categoryId);

        if (!$category instanceof VehicleCategory) {
            return $this->json(['error' => 'Vehicle category not found.'], Response::HTTP_NOT_FOUND);
        }

        $car->setCategory($category);
        $car->setMake($payload->make);
        $car->setModel($payload->model);
        $car->setRegistrationNumber($payload->registrationNumber);
        $car->setModelYear($payload->modelYear);
        $car->setDailyRate($payload->dailyRate);
        $car->setStatus($payload->status);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'A car with that registration number already exists.'], Response::HTTP_CONFLICT);
        }

        return $this->json($this->toArray($car));
    }

    #[Route('/cars/{id}', name: 'cars_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $car = $entityManager->find(Car::class, $id);

        if (!$car instanceof Car) {
            return $this->notFound();
        }

        $entityManager->remove($car);

        try {
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->json(
                ['error' => 'This car is referenced by a rental and cannot be deleted.'],
                Response::HTTP_CONFLICT,
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /** @return array<string, int|string> */
    private function toArray(Car $car): array
    {
        return [
            'id' => $car->getId(),
            'categoryId' => $car->getCategory()->getId(),
            'category' => $car->getCategory()->getName(),
            'make' => $car->getMake(),
            'model' => $car->getModel(),
            'registrationNumber' => $car->getRegistrationNumber(),
            'modelYear' => $car->getModelYear(),
            'dailyRate' => $car->getDailyRate(),
            'status' => $car->getStatus(),
        ];
    }

    private function notFound(): JsonResponse
    {
        return $this->json(['error' => 'Car not found.'], Response::HTTP_NOT_FOUND);
    }
}
