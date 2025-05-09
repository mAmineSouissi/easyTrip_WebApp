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

            $score = 100 - $count; 

            if (in_array($date->format('N'), [6, 7])) {
                $score -= 20;
            }
            $daysAhead = (int)(new \DateTime())->diff($date)->format('%a');
            $score += min($daysAhead, 10); // max +10

            $scores['xxxx-' . $date->format('m-d')] = $score;

        }
        arsort($scores);
        // Retourne la meilleure date
        return key($scores);
    }
}