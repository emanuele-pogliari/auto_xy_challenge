<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(type: "integer")]
    private int $year;

   #[ORM\Column(type: "integer")]
   private int $price;
   
   #[ORM\Column(type: "boolean")]
   private bool $isAvailable = true;


   #[ORM\ManyToOne(targetEntity: CarModel::class, inversedBy: 'cars')]
    private CarModel $model;

}
