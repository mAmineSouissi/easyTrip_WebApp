<?php

namespace App\Repository;

use App\Entity\Reclamation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function save(Reclamation $reclamation, bool $flush = false): void
    {
        $this->_em->persist($reclamation);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Reclamation $reclamation, bool $flush = false): void
    {
        $this->_em->remove($reclamation);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function searchAndSortQuery(?string $search, ?string $sortField = 'date', ?string $sortOrder = 'DESC'): Query
    {
        $qb = $this->createQueryBuilder('r');

        if ($search) {
            $qb->andWhere('r.issue LIKE :search OR r.category LIKE :search OR r.status LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $allowedFields = ['category', 'status', 'date'];
        if (in_array($sortField, $allowedFields)) {
            $qb->orderBy('r.' . $sortField, strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC');
        }

        return $qb->getQuery();
    }

    public function searchAndSortByUserQuery(User $user, ?string $keyword, string $sort = 'date', string $order = 'DESC'): Query
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.userid = :user')
            ->setParameter('user', $user);

        if ($keyword) {
            $qb->andWhere('r.issue LIKE :keyword OR r.category LIKE :keyword')
               ->setParameter('keyword', '%' . $keyword . '%');
        }

        $allowedFields = ['category', 'status', 'date'];
        if (in_array($sort, $allowedFields)) {
            $qb->orderBy('r.' . $sort, strtoupper($order) === 'ASC' ? 'ASC' : 'DESC');
        }

        return $qb->getQuery();
    }
}
