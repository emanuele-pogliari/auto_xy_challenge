<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\CarModel;
use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/add', methods: ['POST'])]
    public function add(Request $request): JsonResponse 
    {
        //convert json in an associative array
        $data = json_decode($request->getContent(), true);

        //create a new car Instance
        $car = new Car();
        $car->setYear($data['year']);
        $car->setPrice($data['price']);
        $car->setIsAvailable($data['isAvailable']);

        //find the model by id
        $model = $this->entityManager->getRepository(CarModel::class)->find($data['model']);
        $car->setModel($model);

        //save the car into the database
        $this->entityManager->persist($car);
        $this->entityManager->flush();


        //json response
        return $this->json(
            $this->resultsForApi([$car]),
            Response::HTTP_CREATED
        );
    }

    #[Route('/car/{id}/edit', methods:['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {

    $car = $this->entityManager->getRepository(Car::class)->find($id);

    $data = json_decode($request->getContent(), true);

    $car->setYear($data['year']);
    $car->setPrice($data['price']);
    $car->setIsAvailable($data['isAvailable']);

    if (isset($data['model'])) {
        $model = $this->entityManager->getRepository(CarModel::class)->find($data['model']);
        $car->setModel($model);
    }

    $this->entityManager->flush();
    return $this->json($this->resultsForApi([$car])[0]);
    }

    #[Route('/car/{id}/delete', methods: ['DELETE'])]
        public function delete(int $id): JsonResponse
        {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        if (!$car) {
            return $this->json(['message' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($car);
        $this->entityManager->flush();
        return $this->json(['message' => 'Car deleted'], Response::HTTP_NO_CONTENT);
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

