<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\Car;
use App\Entity\CarModel;
use App\Entity\Brand;
use App\Repository\CarRepository;


#[Route('/api',)]
class ApiCarController extends AbstractController
{
    #[Route('/cars', methods: ['GET'])]
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
