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
    public function index(Request $request): JsonResponse
    {

        $brandName = $request->query->get('brand_name');
        $modelName = $request->query->get('model_name');
        $minPrice = $request->query->get('min_price');
        $maxPrice = $request->query->get('max_price');
        $isAvailable = $request->query->get('is_available');
        $year = $request->query->get('year');
        $minYear = $request->query->get('min_year');
        $maxYear = $request->query->get('max_year');


        $cars = $this->carRepository->findByFilters(
            $brandName,
            $modelName,
            $minPrice,
            $maxPrice,
            $isAvailable,
            $year,
            $minYear,
            $maxYear
        );

        return $this->json($this->resultsForApi($cars));
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

        //check if json is valid
        if($data === null){
            return $this->json(['message' => 'Invalid JSON'], 400);
        }


        $requiredFields = ['model_id', 'year', 'price', 'isAvailable'];

        //array for keeping tracks of missing fields
        $missingParameters = [];

        //check if all required fields are present in the request data
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])){
                $missingParameters[] = $field;
            }
        }

        //if missing fields found, return error response
        if(!empty($missingParameters)){
            return $this->json(['message' => 'Missing required fields: '. implode(', ', $missingParameters)], 400);
        }

        //find the model by id
        $model = $this->entityManager->getRepository(CarModel::class)->find($data['model_id']);

        //if model not found, return error response
        if (!$model) {
            return $this->json([
                'message' => 'Model not found',
                'model_id' => $data['model_id']
            ], 404);
        }

        try {
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
        return $this->json([ 
            'message' => 'Car added successfully to the catalog',
            'data' => $this->resultsForApi([$car])
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Errore durante il salvataggio',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/car/{id}/update', methods: ['PUT'])]
    public function updateCar(int $id, Request $request): JsonResponse
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        if (!$car) {
            return $this->json([
                'error' => 'Car not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json([
                'error' => 'JSON non valido'
            ], Response::HTTP_BAD_REQUEST);
        }    

        $this->entityManager->persist($car);
        $this->entityManager->flush();
        
        return $this->json([ 
            'message' => 'Car modified successfully',
            'data' => $this->resultsForApi([$car])
            ], 200);
    }

    #[Route('/car/{id}/delete', methods: ['DELETE'])]
    public function deleteCar(int $id): JsonResponse
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        $this->entityManager->remove($car);
        $this->entityManager->flush();
        
        return $this->json([
            'message' => 'Car deleted',
            'data' => $this->resultsForApi([$car])
        ],);
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
