<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api')]
class CarController extends AbstractController
{
    public function __construct(
        private readonly CarRepository $carRepository
    ){}

    //API ROUTES

    //get all cars
    #[Route('/cars', name: 'api_cars_list', methods: ['GET'])]
    public function getCars(): JsonResponse
    {
        return $this->json($this->resultsForApi($this->carRepository->findAll()));

    }

    private function resultsForApi(array $cars) : array
    {
        return array_map([$this, 'singleCar'], $cars);
    }
    //returning data of the single car
    private function singleCar(Car $car): array
    {            
        return [
            'id' => $car->getId(),
            'year' => $car->getYear(),
            'price' => $car->getPrice(),
            'isAvailable' => $car->isAvailable(),
            'model' => [
                'id' => $car->getModel()->getId(),
                'name' => $car->getModel()->getName(),
            ],
            'brand' => [
                'id' => $car->getModel()->getBrand()->getId(),
                'name' => $car->getModel()->getBrand()->getName(),
            ],
        ];
    }


    //WEB ROUTES
    #[Route('/cars', name: 'car_list')]
    public function carList(CarRepository $carRepository): Response
    {
        $cars = $carRepository->findAll();
    
        return $this->render('cars/index.html.twig', [
            'cars' => $cars
        ]);
    }

    #[Route('/cars/add', name: 'car_add')]
    public function addCar(): Response
    {
        return $this->render('cars/create.html.twig');
    }

    #[Route('/cars/{id}', name: 'car_show', methods: ['GET'])]
    public function showCar(int $id): Response
    {
        return $this->render('cars/show.html.twig', ['id' => $id]);
    }

    #[Route('/cars/{id}/edit', name: 'car_edit', methods: ['POST', 'GET'])]
    public function editCar(int $id): Response
    {
        return $this->render('cars/edit.html.twig', ['id' => $id]);
    }
}    

