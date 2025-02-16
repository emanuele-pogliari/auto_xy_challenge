<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Car>
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    public function findByFilters(
        ?string $brandName = null,
        ?string $modelName = null,
        ?int $minPrice = null,
        ?int $maxPrice = null,
        ?bool $isAvailable = null,
        ?int $year = null,
        ?int $minYear = null,
        ?int $maxYear = null,
        int $page = 1,
        int $limit = 5

        //will return a Paginator instance with cars filtered (if filters are applied)
      ): Paginator {
          $qb = $this->createQueryBuilder('c');
  
          
        if ($brandName) {
            $qb
                ->join('c.model', 'm')
                ->join('m.brand', 'b')
                ->andWhere('b.name =:brandName')
                ->setParameter('brandName', $brandName);
        }

        if ($modelName) {
            $qb
                ->join('c.model', 'm')
                ->andWhere('m.name =:modelName')
                ->setParameter('modelName', $modelName);
        }

  
          if ($minPrice) {
              $qb
                  ->andWhere('c.price >=:minPrice')
                  ->setParameter('minPrice', $minPrice);
          }
  
          if ($maxPrice) {
              $qb
                  ->andWhere('c.price <=:maxPrice')
                  ->setParameter('maxPrice', $maxPrice);
          }
  
          if ($isAvailable!== null) {
              $qb
                  ->andWhere('c.isAvailable =:isAvailable')
                  ->setParameter('isAvailable', $isAvailable);
          }

          if ($year) {
              $qb
                  ->andWhere('c.year =:year')
                  ->setParameter('year', $year);
          }
  
          if ($minYear) {
              $qb
                  ->andWhere('c.year >=:minYear')
                  ->setParameter('minYear', $minYear);
          }
  
          if ($maxYear) {
              $qb
                  ->andWhere('c.year <=:maxYear')
                  ->setParameter('maxYear', $maxYear);
          }

          $qb->setFirstResult(($page - 1) * $limit)
          ->setMaxResults($limit);
  
          return new Paginator($qb->getQuery());
      }

    //    /**
    //     * @return Car[] Returns an array of Car objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Car
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
