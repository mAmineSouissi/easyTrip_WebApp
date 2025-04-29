<?php

namespace App\Repository;

use App\Entity\OfferTravel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Offer_travelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OfferTravel::class);
    }

    // Offer_travelRepository.php
public function searchAndFilter(?string $search, array $categories, ?bool $onlyWithPromotion)
{
    $qb = $this->createQueryBuilder('o')
        ->orderBy('o.departure_date', 'ASC');

    if (!empty($search)) {
        $qb->andWhere('o.destination LIKE :search')
           ->setParameter('search', '%'.addcslashes($search, '%_').'%');
    }

    if (!empty($categories)) {
        $qb->andWhere('o.category IN (:categories)')
           ->setParameter('categories', $categories);
    }

    // Modification ici - ne filtre que si onlyWithPromotion est true
    if ($onlyWithPromotion === true) {
        $qb->andWhere('o.promotion IS NOT NULL');
    }
    // Si false ou null, on ne filtre pas les promotions

    return $qb->getQuery()->getResult();
}
}