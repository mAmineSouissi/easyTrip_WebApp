<?php

namespace App\Repository;

use App\Entity\Res_transport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Res_transportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Res_transport::class);
    }

    // Add custom methods as needed
}