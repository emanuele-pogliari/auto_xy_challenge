<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Car;
use App\Entity\CarModel;
use App\Repository\CarRepository;


#[Route('/api', name: 'api_')]
class ApiCarController extends AbstractController
{
    #[Route('/cars', name: 'api_cars_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
    }

    #[Route('/car{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
    }
    
    #[Route('/add', methods: ['POST'])]
    public function addCar(): JsonResponse
    {
    }

    #[Route('car/{id}/update', methods: ['PUT', 'PATCH'])]
    public function updateCar(int $id, Request $request): JsonResponse
    {
    }

    #[Route('/car/{id}/delete', methods: ['DELETE'])]
    public function deleteCar(int $id): JsonResponse
    {
    }

}
