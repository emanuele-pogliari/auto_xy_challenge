<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;


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

        //validation for pagination parameters
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        if($page < 1){
            return $this->json([
                'error' => 'Invalid page number',
            ], 400);
        }

        if($limit < 1 || $limit > 30){
            return $this->json([
                'error' => 'Invalid limit number',
            ], 400);
        }

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
            $maxYear,
            $page,
            $limit
        );

        // total number of the pages
        $totalPages = ceil($cars->count() / $limit);

        if($page > $totalPages){
            return $this->json([
                'error' => 'Invalid page number',
            ], 400);
        }


        $response = [
            'cars' => $this->resultsForApi($cars->getIterator()->getArrayCopy()),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit,
        ];

        return $this->json($response);
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


        $priceTransformer = new \App\Form\DataTransformer\PriceTransformer();
        $priceInCents = $priceTransformer->reverseTransform($data['price']);

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
        $car->setPrice($priceInCents);
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
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json([
                'error' => 'JSON non valido'
            ], 400);
        }    

        try{
            if (isset($data['year'])) {
                $car->setYear($data['year']);
            }
            
            if (isset($data['price'])) {
                $car->setPrice($data['price']);
            }
            
            if (isset($data['isAvailable'])) {
                $car->setIsAvailable($data['isAvailable']);
            }
            
            if (isset($data['model_id'])) {
                $model = $this->entityManager->getRepository(CarModel::class)->find($data['model_id']);
                //check if the model exists in the database
                if (!$model) {
                    return $this->json([
                        'error' => 'Model not found'
                    ], 404);
                }
                $car->setModel($model);
            }

        $this->entityManager->persist($car);
        $this->entityManager->flush();
        
        return $this->json([ 
            'message' => 'Car modified successfully',
            'data' => $this->resultsForApi([$car])
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred',
               'message' => $e->getMessage()
            ], 500);
        }
    
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
                'price' => number_format($car->getPrice() / 100, 2, '.', '') . " Eur",
                'isAvailable' => $car->isAvailable(),
            ];
        }, $cars);
         
    }

}
