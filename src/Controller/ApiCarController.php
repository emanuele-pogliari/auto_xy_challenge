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

use App\Form\DataTransformer\PriceTransformer;


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
    public function addCar(Request $request, PriceTransformer $priceTransformer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        //check if json is valid
        if($data === null){
            return $this->json(['message' => 'Invalid JSON'], 400);
        }

        $requiredFields = ['year', 'price', 'isAvailable'];

        //array for keeping tracks of missing fields
        $missingParameters = [];

        //check if all required fields are present in the request data
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])){
                $missingParameters[] = $field;
            }
        }

        if (isset($data['brand_id']) && isset($data['brand_name'])) {
            return $this->json(['error' => 'Only one of brand_id or brand_name should be provided'], 400);
        } 
    
        elseif (!isset($data['brand_id']) &&!isset($data['brand_name'])) {
            $missingParameters = 'brand_id or brand_name';
        }

        // Check if either model_id or model_name is present, but not both
        if (isset($data['model_id']) && isset($data['model_name'])) {
            return $this->json(['error' => 'Only one of model_id or model_name should be provided'], 400);
        }
    
        elseif (!isset($data['model_id']) &&!isset($data['model_name'])) {
            $missingParameters = 'model_id or model_name';
        }

        //return the missing parameters
        if (!empty($missingParameters)) {
            return $this->json([
                'error' => 'Missing required fields',      
                'missing_fields' => $missingParameters
            ], 400);
        }

        //brand
        if (isset($data['brand_id'])) {
            $brand = $this->entityManager->getRepository(Brand::class)->find($data['brand_id']);

            if (!$brand) {
                return $this->json([
                    'error' => 'Brand not found',
                    'brand_id' => $data['brand_id']
                ], 404);
            }

        } elseif (isset($data['brand_name'])) {
            $brand = $this->entityManager->getRepository(Brand::class)
                ->findOneBy(['name' => $data['brand_name']]);

            if (!$brand) {
                $brand = new Brand();
                $brand->setName($data['brand_name']);
                $this->entityManager->persist($brand);
                $this->entityManager->flush();
            }
        } 

        //car model
        if (isset($data['model_id'])) {
            $model = $this->entityManager->getRepository(CarModel::class)->find($data['model_id']);

            if (!$model) {
                return $this->json([
                    'error' => 'Model not found',
                    'model_id' => $data['model_id']
                ], 404);
            }

        } elseif (isset($data['model_name'])) {

            if (!$brand) {  
                return $this->json(['error' => 'Brand is required to create a new model'], 400);
            }
    
            $model = $this->entityManager->getRepository(CarModel::class)
                ->findOneBy([
                    'name' => $data['model_name'],
                    'brand' => $brand
                ]);
            if (!$model) {
                $model = new CarModel();
                $model->setName($data['model_name']);
                $model->setBrand($brand);
                $this->entityManager->persist($model);
            }
        }

        $priceInCents = $priceTransformer->reverseTransform($data['price']);

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
                'error' => 'An Error Occured',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/car/{id}/update', methods: ['PUT'])]
    public function updateCar(int $id, Request $request, PriceTransformer $priceTransformer): JsonResponse
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

        $allowedFields = ['model_name', 'brand_name', 'year', 'price', 'isAvailable'];
    
        $invalidFields = array_diff(array_keys($data), $allowedFields);
        
        if (!empty($invalidFields)) {
            return $this->json([
                'error' => 'Invalid fields provided',
                'invalid_fields' => $invalidFields
            ], 400);
        }
    

        try{
            
            if (isset($data['year'])) {
                $car->setYear($data['year']);
            }
            
            if (isset($data['price'])) {
                $car->setPrice($priceTransformer->reverseTransform($data['price']));
            }
            
            if (isset($data['isAvailable'])) {
                $car->setIsAvailable($data['isAvailable']);
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
