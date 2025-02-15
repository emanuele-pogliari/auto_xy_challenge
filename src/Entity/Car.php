<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    #[Assert\Range(min: 1900, max: 2100, notInRangeMessage: "Year must be between {{ min }} and {{ max }}")]
    private int $year;

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "Price cannot be null")]
    #[Assert\Positive(message: "Price must be a positive number")]
    #[Assert\LessThanOrEqual(1000000, message: "Price cannot be higher than 1000000")]
    private int $price;

    #[ORM\Column(type: "boolean")]
    private bool $isAvailable = true;

    #[ORM\ManyToOne(targetEntity: CarModel::class, inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Assert\NotNull(message: "A car must be associated with a valid model")]
    private CarModel $model;

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getStatus(): string
    {
        return $this->isAvailable ? "Available" : "Sold";
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


