<?php

namespace App\Repository;

use App\Entity\Feedback;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    /**
     * Feedbacks globaux avec recherche, tri et filtre par type (admin)
     */
    public function findBySearchSortAndType(?string $search, string $sort = 'date', string $dir = 'DESC', ?string $type = null): Query
    {
        $allowedFields = ['message', 'rating', 'date'];
        if (!in_array($sort, $allowedFields)) $sort = 'date';

        $qb = $this->createQueryBuilder('f');

        if ($search) {
            $qb->andWhere('f.message LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($type === 'hotel') {
            $qb->andWhere('f.hotel IS NOT NULL');
        } elseif ($type === 'ticket') {
            $qb->andWhere('f.ticket IS NOT NULL');
        } elseif ($type === 'travel') {
            $qb->andWhere('f.travel IS NOT NULL');
        }

        return $qb->orderBy('f.' . $sort, strtoupper($dir))->getQuery();
    }

    /**
     * Feedbacks globaux avec recherche et tri (ancienne version sans filtre type)
     */
    public function findBySearchAndSort(?string $search, string $sort = 'date', string $dir = 'DESC'): Query
    {
        $allowedFields = ['message', 'rating', 'date'];
        if (!in_array($sort, $allowedFields)) $sort = 'date';

        $qb = $this->createQueryBuilder('f');

        if ($search) {
            $qb->andWhere('f.message LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('f.' . $sort, strtoupper($dir))->getQuery();
    }

    /**
     * Tous les feedbacks d’un utilisateur (non triés)
     */
    public function findByUser(int $userId): Query
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('f.date', 'DESC')
            ->getQuery();
    }

    /**
     * Feedbacks d’un utilisateur avec recherche et tri
     */
    public function findByUserAndSearch(User $user, ?string $search, string $sort = 'date', string $dir = 'DESC'): array
    {
        $allowedFields = ['message', 'rating', 'date'];
        if (!in_array($sort, $allowedFields)) $sort = 'date';

        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.userId = :user')
            ->setParameter('user', $user);

        if ($search) {
            $qb->andWhere('f.message LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('f.' . $sort, strtoupper($dir))
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Feedbacks liés à un agent (en SQL natif)
     */
    public function findByAgentRaw(int $agentId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT f.* FROM feedback f
            LEFT JOIN hotels h ON f.hotel_id = h.id_hotel
            LEFT JOIN tickets t ON f.ticket_id = t.id_ticket
            WHERE h.user_id = :agentId OR t.user_id = :agentId
            ORDER BY f.date DESC
        ";

        return $conn->executeQuery($sql, ['agentId' => $agentId])->fetchAllAssociative();
    }

    /**
     * Feedbacks d’une offre spécifique
     */
    public function findFeedbacksByOffer(string $type, int $id): array
    {
        $qb = $this->createQueryBuilder('f');

        switch ($type) {
            case 'hotel':
                $qb->andWhere('f.hotel = :id');
                break;
            case 'ticket':
                $qb->andWhere('f.ticket = :id');
                break;
            case 'travel':
                $qb->andWhere('f.travel = :id');
                break;
            default:
                throw new \InvalidArgumentException("Type d'offre inconnu");
        }

        return $qb->setParameter('id', $id)
                  ->orderBy('f.date', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Feedbacks d’une offre avec tri + recherche
     */
    public function findFeedbacksByOfferFiltered(string $type, int $id, ?string $search, string $sort, string $dir): array
    {
        $allowedSorts = ['date', 'rating'];
        if (!in_array($sort, $allowedSorts)) $sort = 'date';

        $qb = $this->createQueryBuilder('f');

        switch ($type) {
            case 'hotel':
                $qb->andWhere('f.hotel = :id');
                break;
            case 'ticket':
                $qb->andWhere('f.ticket = :id');
                break;
            case 'travel':
                $qb->andWhere('f.travel = :id');
                break;
            default:
                throw new \InvalidArgumentException("Type d'offre invalide");
        }

        $qb->setParameter('id', $id);

        if ($search) {
            $qb->andWhere('f.message LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('f.' . $sort, strtoupper($dir))
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Nombre total de feedbacks pour une offre
     */
    public function countByOffer(string $type, int $id): int
    {
        $qb = $this->createQueryBuilder('f');

        switch ($type) {
            case 'hotel':
                $qb->andWhere('f.hotel = :id');
                break;
            case 'ticket':
                $qb->andWhere('f.ticket = :id');
                break;
            case 'travel':
                $qb->andWhere('f.travel = :id');
                break;
            default:
                return 0;
        }

        return (int) $qb
            ->setParameter('id', $id)
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Top 5 des offres les mieux notées par un agent
     */
    public function getTopRatedOffersByAgent(int $agentId, int $limit = 5): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 'hotel' AS type, f.hotel_id AS offer_id, AVG(f.rating) AS avg_rating
            FROM feedback f
            INNER JOIN hotels h ON f.hotel_id = h.id_hotel
            WHERE h.user_id = :agentId
            GROUP BY f.hotel_id

            UNION ALL

            SELECT 'ticket' AS type, f.ticket_id AS offer_id, AVG(f.rating) AS avg_rating
            FROM feedback f
            INNER JOIN tickets t ON f.ticket_id = t.id_ticket
            WHERE t.user_id = :agentId
            GROUP BY f.ticket_id

            UNION ALL

            SELECT 'travel' AS type, f.travel_id AS offer_id, AVG(f.rating) AS avg_rating
            FROM feedback f
            INNER JOIN offer_travel tr ON f.travel_id = tr.id
            WHERE tr.user_id = :agentId
            GROUP BY f.travel_id

            ORDER BY avg_rating DESC
            LIMIT {$limit}
        ";

        return $conn->executeQuery($sql, ['agentId' => $agentId])->fetchAllAssociative();
    }

    
    public function getAdminFeedbacksArray(?string $search = null, string $sort = 'date', string $dir = 'DESC'): array
    {
        return $this->findBySearchAndSort($search, $sort, $dir)->getResult();
    }

    public function getUserFeedbackCount(int $userId): int
{
    return $this->createQueryBuilder('f')
        ->select('COUNT(f.id)')
        ->andWhere('f.userId = :userId')
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getSingleScalarResult();
}

public function getFeedbackCountByDate(): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = "
        SELECT DATE(f.date) as feedback_date, COUNT(*) as total
        FROM feedback f
        GROUP BY feedback_date
        ORDER BY feedback_date ASC
    ";

    return $conn->executeQuery($sql)->fetchAllAssociative();
}


public function getMonthlyComparison(): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = "
        SELECT DATE_FORMAT(date, '%Y-%m') AS month, COUNT(*) AS total
        FROM feedback
        WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 2 MONTH)
        GROUP BY month
        ORDER BY month DESC
        LIMIT 2
    ";

    return $conn->executeQuery($sql)->fetchAllAssociative();
}

public function findNegativeFeedbacks(): array
{
    $keywords = ['nul', 'horrible', 'attente', 'problème', 'retard', 'sale', 'déçu', 'décevant','Catastrophique	','mauvais'];
    $qb = $this->createQueryBuilder('f');
    $orX = $qb->expr()->orX();

    foreach ($keywords as $index => $word) {
        $orX->add($qb->expr()->like('f.message', ":kw$index"));
        $qb->setParameter("kw$index", "%$word%");
    }

    return $qb->where($orX)
              ->orderBy('f.date', 'DESC')
              ->getQuery()
              ->getResult();
}


}
