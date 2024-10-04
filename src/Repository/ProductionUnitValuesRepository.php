<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductionUnit;
use App\Entity\ProductionUnitValues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductionUnitValuesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionUnitValues::class);
    }

    public function findOneByProductionUnitAndStartDate(ProductionUnit $productionUnit, \DateTimeImmutable $startDate): ProductionUnitValues
    {
        return $this->createQueryBuilder('puv')
            ->andWhere('puv.productionUnit = :productionUnit')
            ->andWhere('puv.startDate = :startDate')
            ->setParameter('productionUnit', $productionUnit)
            ->setParameter('startDate', $startDate)
            ->getQuery()
            ->getSingleResult();
    }

    public function save(ProductionUnitValues $productionUnitValues): void
    {
        $this->getEntityManager()->persist($productionUnitValues);
        $this->getEntityManager()->flush();
    }
}
