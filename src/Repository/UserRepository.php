<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;

class UserRepository extends ServiceEntityRepository
{
    private $em;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
        $this->em = $this->getEntityManager();
    }

 

    public function getUserById(int $id): ?User
    {
        return $this->find($id);
    }

    public function getAllUsers(): array
    {
        return $this->findAll();
    }
    public function getUserByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function countByRole(): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('u.role, COUNT(u.id) as count')
            ->groupBy('u.role');
        
        $results = $queryBuilder->getQuery()->getResult();
        
        // Format results into a simple array
        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[$result['role']] = (int)$result['count'];
        }
        
        return $formattedResults;
    }

  
}
