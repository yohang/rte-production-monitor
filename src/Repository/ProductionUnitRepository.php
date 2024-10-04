<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductionUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final class ProductionUnitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionUnit::class);
    }

    public function findOneByEicCode(string $eicCode): ProductionUnit
    {
        return $this->createQueryBuilder('pu')
            ->andWhere('pu.eicCode = :eicCode')
            ->setParameter('eicCode', $eicCode)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @return Uuid[]
     */
    public function findAllIds(): array
    {
        return $this->createQueryBuilder('pu')
            ->select('pu.id')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function save(ProductionUnit $productionUnit): void
    {
        $this->getEntityManager()->persist($productionUnit);
        $this->getEntityManager()->flush();
    }
}
