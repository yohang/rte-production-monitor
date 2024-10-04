<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductionSubUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProductionSubUnitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionSubUnit::class);
    }

    public function findOneByEicCode(string $eicCode): ProductionSubUnit
    {
        return $this->createQueryBuilder('psu')
            ->andWhere('psu.eicCode = :eicCode')
            ->setParameter('eicCode', $eicCode)
            ->getQuery()
            ->getSingleResult();
    }

    public function save(ProductionSubUnit $productionSubUnit): void
    {
        $this->getEntityManager()->persist($productionSubUnit);
        $this->getEntityManager()->flush();
    }
}
