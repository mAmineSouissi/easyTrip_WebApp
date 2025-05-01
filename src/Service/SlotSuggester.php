<?php

namespace App\Service;

use App\Repository\ReservationRepository;

class SlotSuggester
{
    public function __construct(private ReservationRepository $reservationRepo) {}

    public function suggestDate(): ?string
    {
        $data = $this->reservationRepo->countReservations();
        $scores = [];

        foreach ($data as $entry) {
            $date = $entry['date'];
            $count = (int)$entry['reservation_count'];

            // Score basé sur une logique simple d'IA heuristique :
            // - moins il y a de réservations, meilleur est le score
            // - évite les week-ends (pondère négativement)
            // - favorise les jours avec peu de réservations récentes

            $score = 100 - $count; // base

            // Pénalise les week-ends
            if (in_array($date->format('N'), [6, 7])) {
                $score -= 20;
            }

            // Bonus si la date est plus éloignée (planning plus flexible)
            $daysAhead = (int)(new \DateTime())->diff($date)->format('%a');
            $score += min($daysAhead, 10); // max +10

            $scores['xxxx-' . $date->format('m-d')] = $score;

        }

        // Trie les dates par score décroissant
        arsort($scores);

        // Retourne la meilleure date
        return key($scores);
    }
}
