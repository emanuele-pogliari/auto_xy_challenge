<?php

namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\CarModel;
use App\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ImportCarToDB extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = json_decode(file_get_contents(__DIR__. '/cars.json'), true);

        foreach ($data as $carData) {

            $brandName = $carData['brand'];


           if (!isset($brands[$brandName])) {
     
            $brand = $manager->getRepository(Brand::class)->findOneBy([
                'name' => $brandName,
            ]);

            if (!$brand) {
                $brand = new Brand();
                $brand->setName($brandName);
                $manager->persist($brand);
            }

            $brands[$brandName] = $brand; 
        } else {

            $brand = $brands[$brandName];
        }

        $carModel = $manager->getRepository(CarModel::class)->findOneBy([
            'brand' => $brand,
            'name' => $carData['model'], 
        ]);

        if (!$carModel) {
            $carModel = new CarModel();
            $carModel->setName($carData['model']); 
            $carModel->setBrand($brand); 
            $manager->persist($carModel);
        }

            $car = new Car();
            $car->setModel($carModel);
            $car->setYear($carData['year']);
            $car->setIsAvailable($carData['isAvailable']);
            $car->setPrice($carData['price']);

            $manager->persist($car);
        }

        $manager->flush();
    }
}