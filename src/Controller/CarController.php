<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api', name: 'api_')]
class CarController extends AbstractController
{
    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly EntityManagerInterface $entityManager
    ){}

    //get all cars
    #[Route('/cars', name: 'api_cars_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json($this->resultsForApi($this->carRepository->findAll()));

    }

    //get a specific car
    #[Route('/car/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        //check if car exists
        if (!$car) {
            return $this->json(['message' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->resultsForApi([$car]));
    }

    //helper method to format the API response
    private function resultsForApi(array $cars) : array
    {
        return array_map(function ($car) {
            return [
                'id' => $car->getId(),
                'brand' => [
                    'id' => $car->getModel()->getBrand()->getId(),
                    'name' => $car->getModel()->getBrand()->getName(),
                ],
                'model' => [
                    'id' => $car->getModel()->getId(),
                    'name' => $car->getModel()->getName(),
                ],
                'year' => $car->getYear(),
                'price' => $car->getPrice(),
                'isAvailable' => $car->isAvailable(),
            ];
        }, $cars);
         
    }


    //WEB ROUTES
    // #[Route('/cars', name: 'car_list')]
    // public function carList(CarRepository $carRepository): Response
    // {
    //     $cars = $carRepository->findAll();
    
    //     return $this->render('cars/index.html.twig', [
    //         'cars' => $cars
    //     ]);
    // }

    // #[Route('/cars/add', name: 'car_add')]
    // public function addCar(): Response
    // {
    //     return $this->render('cars/create.html.twig');
    // }

    // #[Route('/cars/{id}', name: 'car_show', methods: ['GET'])]
    // public function showCar(int $id): Response
    // {
    //     return $this->render('cars/show.html.twig', ['id' => $id]);
    // }

    // #[Route('/cars/{id}/edit', name: 'car_edit', methods: ['POST', 'GET'])]
    // public function editCar(int $id): Response
    // {
    //     return $this->render('cars/edit.html.twig', ['id' => $id]);
    // }
}    

