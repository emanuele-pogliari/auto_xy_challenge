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

    //web route
    #[Route('/cars', name: 'car_list')]
    public function carList(CarRepository $carRepository): Response
    {
        $cars = $carRepository->findAll();
    
        return $this->render('cars/index.html.twig', [
            'cars' => $cars
        ]);
    }
}    

