<?php

namespace App\Repository;

use App\Entity\Tickets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;

class TicketsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tickets::class);
    }

    public function searchAndFilter(
        string $search,
        array $ticketClasses,
        array $ticketTypes,
        ?Collection $agencies = null,
        array $agenciesFilter = []
    ): array {
        $qb = $this->createQueryBuilder('t');

        if ($search) {
            $qb->andWhere('t.airline LIKE :search OR t.departureCity LIKE :search OR t.arrivalCity LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($ticketClasses) {
            $qb->andWhere('t.ticketClass IN (:ticketClasses)')
               ->setParameter('ticketClasses', $ticketClasses);
        }

        if ($ticketTypes) {
            $qb->andWhere('t.ticketType IN (:ticketTypes)')
               ->setParameter('ticketTypes', $ticketTypes);
        }

        // Filtrer par agences pour les agents
        if ($agencies !== null) {
            if ($agencies->isEmpty()) {
                return [];
            }
            $agencyIds = $agencies->map(function ($agency) {
                return $agency->getId();
            })->toArray();

            if (empty($agencyIds)) {
                return [];
            }

            $qb->andWhere('t.agencyId IN (:agencyIds)')
               ->setParameter('agencyIds', $agencyIds);
        }

        // Filtrer par agences sélectionnées
        if (!empty($agenciesFilter)) {
            $qb->andWhere('t.agencyId IN (:agenciesFilter)')
               ->setParameter('agenciesFilter', $agenciesFilter);
        }

        return $qb->getQuery()->getResult();
    }

    // Add custom methods as needed
}