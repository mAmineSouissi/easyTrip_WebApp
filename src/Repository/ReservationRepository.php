<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }


    public function countReservationsByDate(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.orderDate as date, COUNT(r.id_reservation) as total')
            ->groupBy('r.orderDate')
            ->orderBy('r.orderDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
    

    public function countReservations(): array
{
    return $this->createQueryBuilder('r')
        ->select('r.orderDate AS date', 'COUNT(r.id_reservation) AS reservation_count')
        ->groupBy('r.orderDate')
        ->orderBy('reservation_count', 'DESC')
        ->getQuery()
        ->getResult();
}

    // Add custom methods as needed
}