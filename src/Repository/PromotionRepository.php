<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Promotion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Promotion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Promotion[]    findAll()
 * @method Promotion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    /**
     * Récupère toutes les promotions actives (non expirées)
     */
    public function findActivePromotions(): array
    {
        return $this->createActivePromotionsQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * Crée un QueryBuilder pour les promotions actives avec filtres optionnels
     */
    public function createActivePromotionsQueryBuilder(
        ?string $searchTerm = null,
        ?float $minDiscount = null,
        ?float $maxDiscount = null,
        ?\DateTimeInterface $minDate = null
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('p')
            ->where('p.valid_until >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('p.valid_until', 'ASC');

        $this->applyCommonFilters($qb, $searchTerm, $minDiscount, $maxDiscount, $minDate);

        return $qb;
    }

    /**
     * Crée un QueryBuilder pour toutes les promotions avec filtres optionnels
     */
    public function createAllPromotionsQueryBuilder(
        ?string $searchTerm = null,
        ?float $minDiscount = null,
        ?float $maxDiscount = null,
        ?\DateTimeInterface $minDate = null
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.valid_until', 'DESC');

        $this->applyCommonFilters($qb, $searchTerm, $minDiscount, $maxDiscount, $minDate);

        return $qb;
    }

    /**
     * Applique les filtres communs à un QueryBuilder
     */
    private function applyCommonFilters(
        QueryBuilder $qb,
        ?string $searchTerm,
        ?float $minDiscount,
        ?float $maxDiscount,
        ?\DateTimeInterface $minDate
    ): void {
        if ($searchTerm) {
            $qb->andWhere('LOWER(p.title) LIKE LOWER(:searchTerm)')
               ->setParameter('searchTerm', '%'.$searchTerm.'%');
        }

        if ($minDiscount !== null) {
            $qb->andWhere('p.discount_percentage >= :minDiscount')
               ->setParameter('minDiscount', $minDiscount);
        }

        if ($maxDiscount !== null) {
            $qb->andWhere('p.discount_percentage <= :maxDiscount')
               ->setParameter('maxDiscount', $maxDiscount);
        }

        if ($minDate) {
            $qb->andWhere('p.valid_until >= :minDate')
               ->setParameter('minDate', $minDate);
        }
    }

    /**
     * Trouve les promotions actives pour un formulaire de sélection
     */
    public function findActiveForForm(): array
    {
        return $this->createActivePromotionsQueryBuilder()
            ->select('p')
            ->getQuery()
            ->getResult();
    }
}