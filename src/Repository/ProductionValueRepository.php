<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductionSubUnit;
use App\Entity\ProductionValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductionValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionValue::class);
    }

    public function findOneByProductionSubUnitAndStartDate(ProductionSubUnit $productionSubUnit, \DateTimeImmutable $startDate): ProductionValue
    {
        return $this->createQueryBuilder('pv')
                    ->andWhere('pv.productionSubUnit = :productionSubUnit')
                    ->andWhere('pv.startDate = :startDate')
                    ->setParameter('productionSubUnit', $productionSubUnit)
                    ->setParameter('startDate', $startDate)
                    ->getQuery()
                    ->getSingleResult();
    }

    public function save(ProductionValue $productionValue): void
    {
        $this->getEntityManager()->persist($productionValue);
        $this->getEntityManager()->flush();
    }
}
