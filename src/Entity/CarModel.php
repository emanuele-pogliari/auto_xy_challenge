<?php

namespace App\Entity;

use App\Repository\CarModelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarModelRepository::class)]
class CarModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    #[ORM\Column(length: 255)]
    private string $name; 

    #[ORM\ManyToOne(targetEntity:  Brand::class, inversedBy: 'models')]
    private Brand $brand;

    #[ORM\OneToMany(targetEntity: Car::class, mappedBy: 'model')]
    private Collection $cars;
}
