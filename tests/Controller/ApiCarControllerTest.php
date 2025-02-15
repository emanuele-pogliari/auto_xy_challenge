<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Brand;
use App\Entity\Car;
use App\Entity\CarModel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;

final class ApiCarControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        $brand = new Brand();
        $brand->setName('Toyota');
        $this->entityManager->persist($brand);

        $model = new CarModel();
        $model->setName('Supra');
        $model->setBrand($brand);
        $this->entityManager->persist($model);

        $car = new Car();
        $car->setYear(2023);
        $car->setPrice(50000);
        $car->setModel($model);
        $this->entityManager->persist($car);

        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->entityManager->createQuery('DELETE FROM App\Entity\Car')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\CarModel')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Brand')->execute();
        
        $this->entityManager->close();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/cars');

        self::assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $car = $this->entityManager
            ->getRepository(Car::class)
            ->findOneBy([]);
            
        $this->client->request('GET', '/api/car/' . $car->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testAddCar(): void
    {
        $data = [
                'year' => 2024,
                'price' => 60000,
                'model_id' => $this->entityManager
                    ->getRepository(CarModel::class)
                    ->findOneBy([])
                    ->getId(),
                'isAvailable' => true,
            ];
        
            $this->client->request(
                'POST',
                '/api/add',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($data)
            );
        
            self::assertResponseIsSuccessful();
            self::assertResponseHeaderSame('Content-Type', 'application/json');
        
            // Verifica che l'auto sia stata effettivamente creata nel database
            $car = $this->entityManager
                ->getRepository(Car::class)
                ->findOneBy(['year' => 2024]);
        
            self::assertNotNull($car);
            self::assertEquals(2024, $car->getYear());
            self::assertEquals(60000, $car->getPrice());
            self::assertTrue($car->isAvailable());
    }
}
