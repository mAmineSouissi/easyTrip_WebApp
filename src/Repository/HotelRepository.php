<?php

namespace App\Repository;

use App\Entity\Hotel;
use App\Entity\Agency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    /**
     * Récupère les hôtels associés à une liste d'agences
     *
     * @param Agency[] $agencies
     * @return Hotel[]
     */
    public function findByAgencies(array $agencies): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.agency IN (:agencies)')
            ->setParameter('agencies', $agencies)
            ->orderBy('h.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 