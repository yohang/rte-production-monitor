<?php

declare(strict_types=1);

namespace App\Repository;

use App\Bridge\RTE\Model\ProductionType;
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

    /**
     * @param array<int, ProductionUnit> $productionUnits
     *
     * @return array<string, float>
     */
    public function findInstalledCapacitiesForProductionUnits(array $productionUnits, ?\DateTimeImmutable $at = null): array
    {
        if ([] === $productionUnits) {
            return [];
        }

        $rows = $this->createQueryBuilder('puv')
            ->select('IDENTITY(puv.productionUnit) as productionUnitId')
            ->addSelect('SUM(puv.installedCapacity) as installedCapacity')
            ->where('puv.productionUnit IN (:productionUnits)')
            ->andWhere('puv.endDate IS NULL OR puv.endDate >= :at')
            ->groupBy('puv.productionUnit')
            ->setParameter('productionUnits', $productionUnits)
            ->setParameter('at', $at ?? new \DateTimeImmutable())
            ->getQuery()
            ->getArrayResult();

        $capacitiesByUnitId = [];

        foreach ($rows as $row) {
            $capacitiesByUnitId[$row['productionUnitId']] = (float) $row['installedCapacity'];
        }

        return $capacitiesByUnitId;
    }

    /**
     * @param array<int, ProductionUnit> $productionUnits
     *
     * @return array<string, ?ProductionType>
     */
    public function findLatestTypesForProductionUnits(array $productionUnits): array
    {
        if ([] === $productionUnits) {
            return [];
        }

        $rows = $this->createQueryBuilder('puv')
            ->select('IDENTITY(puv.productionUnit) as productionUnitId')
            ->addSelect('puv.type as type')
            ->where('puv.productionUnit IN (:productionUnits)')
            ->andWhere(
                'puv.startDate = (
                    SELECT MAX(puv2.startDate)
                    FROM App\\Entity\\ProductionUnitValues puv2
                    WHERE puv2.productionUnit = puv.productionUnit
                )'
            )
            ->setParameter('productionUnits', $productionUnits)
            ->getQuery()
            ->getArrayResult();

        $typesByUnitId = [];

        foreach ($rows as $row) {
            $typesByUnitId[$row['productionUnitId']] = match (true) {
                $row['type'] instanceof ProductionType => $row['type'],
                null === $row['type'] => null,
                default => ProductionType::from($row['type']),
            };
        }

        return $typesByUnitId;
    }

    public function save(ProductionUnitValues $productionUnitValues): void
    {
        $this->getEntityManager()->persist($productionUnitValues);
        $this->getEntityManager()->flush();
    }
}
