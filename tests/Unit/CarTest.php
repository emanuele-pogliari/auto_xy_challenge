<?php

namespace App\Tests\Unit;

use App\Entity\Car;
use App\Entity\CarModel;
use App\Entity\Brand;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

class CarTest extends TestCase
{
    private Car $car;
    private CarModel $carModel;
    private Brand $brand;

    protected function setUp(): void
    {
        $this->brand = new Brand();
        $this->brand->setName('Toyota');

        $this->carModel = new CarModel();
        $this->carModel->setName('Supra');
        $this->carModel->setBrand($this->brand);
        $this->brand->addModel($this->carModel);

        $this->car = new Car();
        $this->car->setYear(2023);
        $this->car->setPrice(50000);
        $this->car->setModel($this->carModel);
    }


    public function testGetStatus()
    {
        $this->assertTrue($this->car->isAvailable());
        $this->assertEquals("Available", $this->car->getStatus());

        $this->car->setIsAvailable(false);
        $this->assertFalse($this->car->isAvailable());
        $this->assertEquals("Sold", $this->car->getStatus());
    }

    public function testCarModelRelationship()
    {
        $this->assertSame($this->carModel, $this->car->getModel());
        $this->assertSame($this->brand, $this->carModel->getBrand());
        $this->assertTrue($this->brand->getModels()->contains($this->carModel));
    }
}