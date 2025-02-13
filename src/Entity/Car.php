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

   public function getYear(): ?int
   {
       return $this->year;
   }

   public function setYear(int $year): static
   {
       $this->year = $year;

       return $this;
   }

   public function getPrice(): ?int
   {
       return $this->price;
   }

   public function setPrice(int $price): static
   {
       $this->price = $price;

       return $this;
   }

   public function isAvailable(): ?bool
   {
       return $this->isAvailable;
   }

   public function setIsAvailable(bool $isAvailable): static
   {
       $this->isAvailable = $isAvailable;

       return $this;
   }

   public function getModel(): ?CarModel
   {
       return $this->model;
   }

   public function setModel(?CarModel $model): static
   {
       $this->model = $model;

       return $this;
   }

}
