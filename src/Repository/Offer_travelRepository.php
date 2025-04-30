<?php

namespace App\Repository;

use App\Entity\OfferTravel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;

class Offer_travelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OfferTravel::class);
    }

    public function searchAndFilter(string $search, array $categories, ?bool $hasPromotion, ?Collection $agencies = null, array $agenciesFilter = []): array
    {
        $qb = $this->createQueryBuilder('o');

        if ($search) {
            $qb->andWhere('o.title LIKE :search OR o.discription LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($categories) {
            $qb->andWhere('o.category IN (:categories)')
               ->setParameter('categories', $categories);
        }

        if ($hasPromotion !== null) {
            if ($hasPromotion) {
                $qb->andWhere('o.promotion IS NOT NULL');
            } else {
                $qb->andWhere('o.promotion IS NULL');
            }
        }

        // Filtrer par agences pour les agents
        if ($agencies !== null) {
            if ($agencies->isEmpty()) {
                // Si l'agent n'a aucune agence, retourner un tableau vide
                return [];
            }
            // Extraire les IDs des agences pour une requête plus fiable
            $agencyIds = $agencies->map(function ($agency) {
                return $agency->getId();
            })->toArray();

            if (empty($agencyIds)) {
                return [];
            }

            $qb->andWhere('o.agency IN (:agencyIds)')
               ->setParameter('agencyIds', $agencyIds);
        }

        // Filtrer par agences sélectionnées (nouveau filtre)
        if (!empty($agenciesFilter)) {
            $qb->andWhere('o.agency IN (:agenciesFilter)')
               ->setParameter('agenciesFilter', $agenciesFilter);
        }

        return $qb->getQuery()->getResult();
    }
}