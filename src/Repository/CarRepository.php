<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Car>
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    public function findCarsByFilter(string $brand, string $model, string $status)
    {
        $queryBuilder = $this->createQueryBuilder('c')
        ->leftJoin('c.model', 'm')
        ->leftJoin('m.brand', 'b');

        if ($brand) {
            $queryBuilder->andWhere('b.name = :brand')
                ->setParameter('brand', '%' . $brand . '%');
        }
        if ($model) {
            $queryBuilder->andWhere('m.name LIKE :model')
                ->setParameter('model', '%' . $model . '%');
        }
        if($status){
            $queryBuilder->andWhere('c.isAvailable = :status')
                ->setParameter('status', $status === 'available');
        }
        if ($minPrice) {
            $queryBuilder->andWhere('c.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $queryBuilder->andWhere('c.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        return $queryBuilder->getQuery()->getResult();
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
