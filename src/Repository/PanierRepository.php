<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PanierRepository extends ServiceEntityRepository
{
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

    public function findAllPromotionsQuery(?string $name = null, ?string $discount = null, ?string $date = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.valid_until', 'DESC');

        if ($name) {
            $qb->andWhere('LOWER(p.title) LIKE LOWER(:name)')
               ->setParameter('name', '%'.$name.'%');
        }

        if ($discount) {
            list($min, $max) = explode('-', $discount);
            $qb->andWhere('p.discount_percentage BETWEEN :min AND :max')
               ->setParameter('min', $min)
               ->setParameter('max', $max);
        }

        if ($date) {
            $qb->andWhere('p.valid_until >= :date')
               ->setParameter('date', new \DateTime($date));
        }

        return $qb->getQuery();
    }

    public function findActivePromotionsQuery(?string $name = null, ?string $discount = null, ?string $date = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.valid_until >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('p.valid_until', 'ASC');

        if ($name) {
            $qb->andWhere('LOWER(p.title) LIKE LOWER(:name)')
               ->setParameter('name', '%'.$name.'%');
        }

        if ($discount) {
            list($min, $max) = explode('-', $discount);
            $qb->andWhere('p.discount_percentage BETWEEN :min AND :max')
               ->setParameter('min', $min)
               ->setParameter('max', $max);
        }

        if ($date) {
            $qb->andWhere('p.valid_until >= :date')
               ->setParameter('date', new \DateTime($date));
        }

        return $qb->getQuery();
    }
}