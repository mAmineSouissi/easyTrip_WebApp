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

    // In Verification_codesRepository.php
    public function findValidCode(string $email, string $code)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.email = :email')
            ->andWhere('v.code = :code')
            ->andWhere('v.expires_at > :now')
            ->andWhere('v.used = :used')
            ->setParameter('email', $email)
            ->setParameter('code', $code)
            ->setParameter('now', new \DateTime())
            ->setParameter('used', false)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeUsedCode(Verification_codes $code): void
    {
        if ($code->getUsed()== 1) {
            $this->getEntityManager()->remove($code);
            $this->getEntityManager()->flush();
        }
    }
    


    // Add custom methods as needed
}
