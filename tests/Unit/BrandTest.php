<?php

namespace App\Tests\Unit;

use App\Entity\Brand;
use App\Entity\CarModel;
use PHPUnit\Framework\TestCase;

class BrandTest extends TestCase
{
    private Brand $brand;
    private CarModel $carModel;

    //setup for the test
    protected function setUp(): void
    {
        $this->brand = new Brand();
        $this->brand->setName('Toyota');

        $this->carModel = new CarModel();
        $this->carModel->setName('Supra');
        $this->carModel->setBrand($this->brand);
    }

    public function testAddCarModel()
    {

        $this->brand->addModel($this->carModel);
        $this->assertCount(1, $this->brand->getModels());

        // Check if the car model was added correctly
        $carModels = $this->brand->getModels();


        $this->assertSame($this->carModel, $carModels->first());

    }
}
