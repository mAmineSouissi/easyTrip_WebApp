<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

// src/Repository/PromotionRepository.php
namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PromotionRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    public function findActivePromotions(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.valid_until >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('p.valid_until', 'ASC')
            ->getQuery()
            ->getResult();
    }
}