<?php

namespace App\Controller;

use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CarController extends AbstractController
{
    //api route
    #[Route('/api/cars', name: 'api_cars_list', methods: ['GET'])]
    public function getCars(CarRepository $carRepository): JsonResponse
    {
        $cars = $carRepository->findAll();

        $data = [];
        foreach ($cars as $car) {
            $data[] = [
                'id' => $car->getId(),
                'year' => $car->getYear(),
                'price' => $car->getPrice(),
                'isAvailable' => $car->isAvailable(),
                'model' => [
                    'id' => $car->getModel()->getId(),
                    'name' => $car->getModel()->getName(),
                    'brand' => [
                        'id' => $car->getModel()->getBrand()->getId(),
                        'name' => $car->getModel()->getBrand()->getName(),
                    ],
                ],
            ];
        }

        return $this->json($data);
    }

    //web routes
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

