<?php

namespace App\Repository;

use App\Entity\Agency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AgencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agency::class);
    }

    public function findAllWithValidEmail(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.email IS NOT NULL')
            ->andWhere('a.email != :empty')
            ->andWhere('a.email LIKE :pattern')
            ->setParameter('empty', '')
            ->setParameter('pattern', '%@%.%')
            ->getQuery()
            ->getResult();
    }
}