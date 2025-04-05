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

    public function addUser(User $user): bool
    {
        try {
            $this->em->persist($user);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    

    public function getUserById(int $id): ?User
    {
        return $this->find($id);
    }

    public function getAllUsers(): array
    {
        return $this->findAll();
    }

    public function updateUser(User $user): void
    {
        $this->em->persist($user); // optional if the user is already managed
        $this->em->flush();
    }

    public function deleteUser(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
