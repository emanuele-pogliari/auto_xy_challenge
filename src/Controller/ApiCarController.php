<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


use App\Entity\Car;
use App\Entity\CarModel;
use App\Entity\Brand;
use App\Repository\CarRepository;


#[Route('/api', name:'_api')]
class ApiCarController extends AbstractController
{

    public function __construct(
        private CarRepository $carRepository,
        private EntityManagerInterface $entityManager,
    ){}

    #[Route('/cars', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json($this->resultsForApi($this->carRepository->findAll()));

    }

    #[Route('/car/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);
        
        return $this->json($this->resultsForApi([$car]));
    }
    
    #[Route('/add', methods: ['POST'])]
    public function addCar(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        //find the model by id
        $model = $this->entityManager->getRepository(CarModel::class)->find($data['model_id']);

        //create a new car Instance
        $car = new Car();
        $car->setYear($data['year']);
        $car->setPrice($data['price']);
        $car->setIsAvailable($data['isAvailable']);
        $car->setModel($model);

        //save the car into the database
        $this->entityManager->persist($car);
        $this->entityManager->flush();


        //json response
        return $this->json(
            $this->resultsForApi([$car])
        );
    }

    #[Route('/car/{id}/update', methods: ['PUT'])]
    public function updateCar(int $id, Request $request): JsonResponse
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        $data = json_decode($request->getContent(), true);

            $car->setYear($data['year']);

            $car->setPrice($data['price']);

            $car->setIsAvailable($data['isAvailable']);
   

        $this->entityManager->flush();
        
        return $this->json($this->resultsForApi([$car]));
    }

    #[Route('/car/{id}/delete', methods: ['DELETE'])]
    public function deleteCar(int $id): JsonResponse
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        $this->entityManager->remove($car);
        $this->entityManager->flush();
        
        return $this->json(['message' => 'Car deleted'],);
    }

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

}
