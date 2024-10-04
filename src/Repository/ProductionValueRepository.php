<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductionSubUnit;
use App\Entity\ProductionUnit;
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

    public function findForUnitBetweenDates(ProductionUnit $productionUnit, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('pv')
                    ->select('pv.startDate')
                    ->addSelect('pv.endDate')
                    ->addSelect('SUM(pv.value) as value')
                    ->innerJoin('pv.productionSubUnit', 'psu')
                    ->andWhere('psu.productionUnit = :productionUnit')
                    ->andWhere('pv.startDate >= :startDate')
                    ->andWhere('pv.startDate <= :endDate')
                    ->groupBy('psu.productionUnit')
                    ->addGroupBy('pv.startDate')
                    ->addGroupBy('pv.endDate')
                    ->orderBy('pv.startDate', 'ASC')
                    ->setParameter('productionUnit', $productionUnit)
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate)
                    ->getQuery()
                    ->getResult();
    }

    public function save(ProductionValue $productionValue): void
    {
        $this->getEntityManager()->persist($productionValue);
        $this->getEntityManager()->flush();
    }
}
