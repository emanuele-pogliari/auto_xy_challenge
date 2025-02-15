<?php

namespace App\Tests\Entity;

use App\Entity\Car;
use App\Entity\CarModel;
use PHPUnit\Framework\TestCase;

class CarModelTest extends TestCase
{
    public function testSetAndGetModel()
    {
        $carModel = new CarModel();
        $carModel->setName('Corolla');

        $car = new Car();
        $car->setModel($carModel);

        $this->assertSame($carModel, $car->getModel());
    }
}