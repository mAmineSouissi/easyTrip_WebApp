<?php

namespace App\Repository;

use App\Entity\Offer_travel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Offer_travelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer_travel::class);
    }

    // Add custom methods as needed
}