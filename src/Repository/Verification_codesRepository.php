<?php

namespace App\Repository;

use App\Entity\Verification_codes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Verification_codesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Verification_codes::class);
    }

    // Add custom methods as needed
}