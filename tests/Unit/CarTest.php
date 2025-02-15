<?php

namespace App\Tests\Unit;

use App\Entity\Car;
use App\Entity\CarModel;
use App\Entity\Brand;
use PHPUnit\Framework\TestCase;

class CarTest extends TestCase
{
    public function testGetStatus()
    {
        $brand = new Brand();
        $brand->setName('Toyota');

        $carModel = new CarModel();
        $carModel->setName('Corolla');
        $carModel->setBrand($brand);

        $car = new Car();
        $car->setModel($carModel);
        $car->setIsAvailable(true);

        $this->assertEquals("Available", $car->getStatus());

        $car->setIsAvailable(false);
        $this->assertEquals("Sold", $car->getStatus());
    }
}