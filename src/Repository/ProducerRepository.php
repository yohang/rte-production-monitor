<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Producer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProducerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producer::class);
    }

    public function findOneByEicCode(string $eicCode): Producer
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.eicCode = :eicCode')
            ->setParameter('eicCode', $eicCode)
            ->getQuery()
            ->getSingleResult();
    }

    public function save(Producer $producer): void
    {
        $this->getEntityManager()->persist($producer);
        $this->getEntityManager()->flush();
    }
}
