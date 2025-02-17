<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
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
    #[OA\Tag(name: "Cars")]
    #[OA\Get(
        path: '/api/cars',
        summary: 'Get list of cars with filters',
        description: 'Retrieves a paginated list of cars with optional filtering'
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Number of items per page',
        schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 50)
    )]
    #[OA\Parameter(
        name: 'brand_name',
        in: 'query',
        description: 'Filter by brand name',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'model_name',
        in: 'query',
        description: 'Filter by model name',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'min_price',
        in: 'query',
        description: 'Minimum price',
        schema: new OA\Schema(type: 'number', minimum: 0)
    )]
    #[OA\Parameter(
        name: 'max_price',
        in: 'query',
        description: 'Maximum price',
        schema: new OA\Schema(type: 'number', minimum: 0)
    )]
    #[OA\Parameter(
        name: 'year',
        in: 'query',
        description: 'Filter by specific year',
        schema: new OA\Schema(type: 'integer', minimum: 1900, maximum: 2025)
    )]
    #[OA\Parameter(
        name: 'min_year',
        in: 'query',
        description: 'Minimum year',
        schema: new OA\Schema(type: 'integer', minimum: 1900)
    )]
    #[OA\Parameter(
        name: 'max_year',
        in: 'query',
        description: 'Maximum year',
        schema: new OA\Schema(type: 'integer', maximum: 2025)
    )]
    #[OA\Parameter(
        name: 'is_available',
        in: 'query',
        description: 'Filter by availability',
        schema: new OA\Schema(type: 'boolean')
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of cars',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'brand', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string')
                        ]),
                        new OA\Property(property: 'model', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string')
                        ]),
                        new OA\Property(property: 'year', type: 'integer'),
                        new OA\Property(property: 'price', type: 'string'),
                        new OA\Property(property: 'isAvailable', type: 'boolean')
                    ]
                )),
                new OA\Property(property: 'metadata', type: 'object', properties: [
                    new OA\Property(property: 'current_page', type: 'integer'),
                    new OA\Property(property: 'total_pages', type: 'integer'),
                    new OA\Property(property: 'total_items', type: 'integer'),
                    new OA\Property(property: 'items_per_page', type: 'integer')
                ])
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid parameters provided',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string'),
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'No cars found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
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

        if ($cars->count() === 0) {
            return $this->json([
                'message' => 'No cars found matching the criteria'
            ], 404);
        }

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
    #[OA\Tag(name: 'Cars')]
    #[OA\Parameter(
    name: 'id',
    description: 'Car ID',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
    response: 200,
    description: 'Returns car details',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(
                        property: 'brand',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string')
                        ]
                    ),
                    new OA\Property(
                        property: 'model',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string')
                        ]
                    ),
                    new OA\Property(property: 'year', type: 'integer'),
                    new OA\Property(property: 'price', type: 'string', example: "50000.00 Eur"),
                    new OA\Property(property: 'isAvailable', type: 'boolean')
                ])
            )
        ]
    )
    )]
    #[OA\Response(
    response: 404,
    description: 'Car not found',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string', example: 'Car not found')
        ]
    )
    )]

    public function show(int $id): JsonResponse
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);
        if ($id <= 0) {
            return $this->json([
                'error' => 'Invalid ID format'
            ], 400);
        }

        if (!$car) {
            return $this->json([
                'error' => 'Car not found',
            ], 404);
        }

        
        return $this->json($this->resultsForApi([$car]));
    }

    
    
    #[Route('/add', methods: ['POST'])]

    #[OA\Tag(name: "Cars")]
    #[OA\Post(
        path: '/api/add',
        summary: 'Create a new car',
        description: 'Creates a new car with the provided details'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'year', type: 'integer', example: 2023),
                    new OA\Property(property: 'price', type: 'number', example: 50000),
                    new OA\Property(property: 'isAvailable', type: 'boolean', example: true)
                ],
                oneOf: [
                    new OA\Schema(
                        properties: [
                            new OA\Property(property: 'brand_id', type: 'integer', example: 1),
                            new OA\Property(property: 'model_id', type: 'integer', example: 1)
                        ],
                        required: ['brand_id', 'model_id']
                    ),
                    new OA\Schema(
                        properties: [
                            new OA\Property(property: 'brand_name', type: 'string', example: 'BMW'),
                            new OA\Property(property: 'model_name', type: 'string', example: 'X5')
                        ],
                        required: ['brand_name', 'model_name']
                    )
                ]
            ),
            examples: [
                new OA\Examples(
                    example: 'with_ids',
                    summary: 'Using IDs',
                    description: "Using IDs requires existing brands and models in the database",
                    value: [
                        'year' => 2023,
                        'price' => 50000,
                        'isAvailable' => true,
                        'brand_id' => 1,
                        'model_id' => 1
                    ]
                ),
                new OA\Examples(
                    example: 'with_names',
                    summary: 'Using Names',
                    description: "Using names allows automatic creation of new brands and models if they don't exist in the database",
                    value: [
                        'year' => 2023,
                        'price' => 50000,
                        'isAvailable' => true,
                        'brand_name' => 'BMW',
                        'model_name' => 'X5'
                    ]
                )
            ]
        )
    )]

    #[OA\Response(
        response: 201,
        description: 'Car created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid data provided',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string'),
                new OA\Property(property: 'missing_fields', type: 'array', items: new OA\Items(type: 'string'))
            ]
        )
    )]

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

        if (isset($data['year'])) {
            if ($data['year'] < 1900 || $data['year'] > date('Y') + 1) {
                return $this->json([
                    'error' => 'Invalid year',
                    'message' => 'Year must be between 1900 and ' . (date('Y') + 1)
                ], 400);
            }
        }

        if (isset($data['price'])) {
            if ($data['price'] < 0) {
                return $this->json([
                    'error' => 'Invalid price',
                    'message' => 'Price cannot be negative'
                ], 400);
            }
        }

        // Check if either brand_id or brand_name is present, but not both
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
    #[OA\Tag(name: 'Cars')]
    #[OA\Put(
        path: '/api/car/{id}/update',
        summary: 'Update an existing car',
        description: 'Updates a car\'s details. All fields are optional - only provided fields will be updated. Brand and model cannot be modified.'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the car to update',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'year',
                    type: 'integer',
                    example: 2023,
                    minimum: 1900,
                    maximum: 2024,
                    description: 'New year of the car'
                ),
                new OA\Property(
                    property: 'price',
                    type: 'number',
                    example: 50000,
                    minimum: 0,
                    description: 'New price in EUR'
                ),
                new OA\Property(
                    property: 'isAvailable',
                    type: 'boolean',
                    example: true,
                    description: 'New availability status'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Car updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Car modified successfully'
                ),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'brand', type: 'string', example: 'BMW'),
                        new OA\Property(property: 'model', type: 'string', example: 'X5'),
                        new OA\Property(property: 'year', type: 'integer', example: 2023),
                        new OA\Property(property: 'price', type: 'string', example: '50000.00 Eur'),
                        new OA\Property(property: 'isAvailable', type: 'boolean', example: true)
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'error',
                    type: 'string',
                    example: 'Invalid fields provided'
                ),
                new OA\Property(
                    property: 'invalid_fields',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['invalidField']
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Car not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'error',
                    type: 'string',
                    example: 'Car not found'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'error',
                    type: 'string',
                    example: 'An error occurred'
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Error message details'
                )
            ]
        )
    )]

    public function updateCar(int $id, Request $request, PriceTransformer $priceTransformer): JsonResponse
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        if ($id <= 0) {
            return $this->json([
                'error' => 'Invalid ID format'
            ], 400);
        }

        if (!$car) {
            return $this->json([
                'error' => 'Car not found',
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json([
                'error' => 'JSON not valid'
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

        if (isset($data['year'])) {
            if ($data['year'] < 1900 || $data['year'] > date('Y') + 1) {
                return $this->json([
                    'error' => 'Invalid year',
                    'message' => 'Year must be between 1900 and ' . (date('Y') + 1)
                ], 400);
            }
        }

        if (isset($data['price'])) {
            if ($data['price'] < 0) {
                return $this->json([
                    'error' => 'Invalid price',
                    'message' => 'Price cannot be negative'
                ], 400);
            }
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
    #[OA\Tag(name: "Cars")]
        #[OA\Delete(
            path: '/api/car/{id}/delete',
            summary: 'Delete a car',
            description: 'Removes a car from the database'
        )]
        #[OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID of the car to delete',
            schema: new OA\Schema(type: 'integer')
        )]
        #[OA\Response(
            response: 200,
            description: 'Car successfully deleted',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Car deleted'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'brand', type: 'string', example: 'BMW'),
                            new OA\Property(property: 'model', type: 'string', example: 'X5'),
                            new OA\Property(property: 'year', type: 'integer', example: 2023),
                            new OA\Property(property: 'price', type: 'string', example: '50000.00 Eur'),
                            new OA\Property(property: 'isAvailable', type: 'boolean', example: true)
                        ]
                    )
                ]
            )
        )]
        #[OA\Response(
            response: 404,
            description: 'Car not found',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Car not found'
                    )
                ]
            )
        )]
        #[OA\Response(
            response: 500,
            description: 'Server error',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'An error occurred'
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Error message details'
                    )
                ]
            )
        )]
    public function deleteCar(int $id): JsonResponse
    {
        if ($id <= 0) {
            return $this->json([
                'error' => 'Invalid ID format'
            ], 400);
        }

        $car = $this->entityManager->getRepository(Car::class)->find($id);

        if (!$car) {
            return $this->json([
                'error' => 'Car not found'
            ], 404);
        }

        try{

            $this->entityManager->remove($car);
            $this->entityManager->flush();
        
            return $this->json([
                'message' => 'Car deleted',
                'data' => $this->resultsForApi([$car])
            ],);
        }  catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }

        
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
