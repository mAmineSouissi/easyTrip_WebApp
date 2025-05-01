<?php

namespace App\Service;

use App\Entity\Hotels;
use App\Entity\Tickets;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChatbotService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function formatHotelData(Hotels $hotel): array
    {
        return [
            'id' => $hotel->getIdHotel(),
            'name' => $hotel->getName() ?? 'Hôtel sans nom',
            'description' => $hotel->getDescription() ?? 'Pas de description disponible',
            'price' => $hotel->getPrice() ?? 0,
            'location' => $hotel->getCity() ?? 'Localisation non spécifiée',
            'rating' => $hotel->getRating() ?? 0,
            'image' => $hotel->getImage() ?? null
        ];
    }

    private function formatTicketData(Tickets $ticket): array
    {
        return [
            'id' => $ticket->getIdTicket(),
            'title' => $ticket->getAirline() ?? 'Ticket sans titre',
            'description' => sprintf(
                'Vol %s de %s à %s',
                $ticket->getFlightNumber(),
                $ticket->getDepartureCity(),
                $ticket->getArrivalCity()
            ),
            'price' => $ticket->getPrice() ?? 0,
            'destination' => $ticket->getArrivalCity() ?? 'Destination non spécifiée',
            'departureDate' => $ticket->getDepartureDate() ? $ticket->getDepartureDate()->format('Y-m-d') : null
        ];
    }

    private function extractPrice(string $message): ?float
    {
        if (preg_match('/moins de (\d+)/i', $message, $matches)) {
            return (float) $matches[1];
        }
        return null;
    }

    private function extractLocation(string $message): ?string
    {
        // Matches city after prepositions like "à", "en", "dans", or "pour"
        if (preg_match('/(?:à|en|dans|pour)\s+([\w\s-]+)/i', $message, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function extractRating(string $message): ?int
    {
        if (preg_match('/(\d+)\s*(?:étoiles?|stars?)/i', $message, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    private function extractDate(string $message): ?\DateTime
    {
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/i', $message, $matches)) {
            try {
                return new \DateTime($matches[3] . '-' . $matches[2] . '-' . $matches[1]);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function processMessage(string $message): array
    {
        $response = [
            'type' => 'text',
            'content' => 'Je ne comprends pas votre demande. Pouvez-vous reformuler ?'
        ];

        try {
            // Détection des mots-clés pour les hôtels
            if (preg_match('/hôtel|hotel|logement|sejour/i', $message)) {
                $queryBuilder = $this->entityManager->getRepository(Hotels::class)->createQueryBuilder('h');
                
                $price = $this->extractPrice($message);
                if ($price !== null) {
                    $queryBuilder->andWhere('h.price <= :price')
                               ->setParameter('price', $price);
                }
                
                $location = $this->extractLocation($message);
                if ($location !== null) {
                    $queryBuilder->andWhere('h.city = :city')
                               ->setParameter('city', $location);
                }
                
                $rating = $this->extractRating($message);
                if ($rating !== null) {
                    $queryBuilder->andWhere('h.rating = :rating')
                               ->setParameter('rating', $rating);
                }

                $hotels = $queryBuilder->getQuery()->getResult();
                
                if (!empty($hotels)) {
                    $formattedHotels = array_map([$this, 'formatHotelData'], $hotels);
                    $response = [
                        'type' => 'hotels',
                        'content' => 'Voici nos offres d\'hôtels disponibles :',
                        'data' => $formattedHotels
                    ];
                } else {
                    $response = [
                        'type' => 'text',
                        'content' => 'Désolé, aucun hôtel ne correspond à vos critères.'
                    ];
                }
            }

            // Détection des mots-clés pour les tickets
            if (preg_match('/ticket|billet|voyage|vol|transport/i', $message)) {
                $queryBuilder = $this->entityManager->getRepository(Tickets::class)->createQueryBuilder('t');
                
                $price = $this->extractPrice($message);
                if ($price !== null) {
                    $queryBuilder->andWhere('t.price <= :price')
                               ->setParameter('price', $price);
                }
                
                $city = $this->extractLocation($message);
                if ($city !== null) {
                    // Exact match for arrival_city to ensure precise filtering
                    $queryBuilder->andWhere('t.arrivalCity = :city')
                               ->setParameter('city', $city);
                }
                
                $date = $this->extractDate($message);
                if ($date !== null) {
                    $queryBuilder->andWhere('t.departureDate = :date')
                               ->setParameter('date', $date);
                }

                $tickets = $queryBuilder->getQuery()->getResult();
                
                if (!empty($tickets)) {
                    $formattedTickets = array_map([$this, 'formatTicketData'], $tickets);
                    $response = [
                        'type' => 'tickets',
                        'content' => 'Voici nos offres de tickets disponibles :',
                        'data' => $formattedTickets
                    ];
                } else {
                    $response = [
                        'type' => 'text',
                        'content' => 'Désolé, aucun ticket ne correspond à vos critères.'
                    ];
                }
            }

            // Suggestions de voyages
            if (preg_match('/suggestion|recommandation|conseil/i', $message)) {
                $hotels = $this->entityManager->getRepository(Hotels::class)
                    ->createQueryBuilder('h')
                    ->orderBy('h.rating', 'DESC')
                    ->setMaxResults(3)
                    ->getQuery()
                    ->getResult();

                $tickets = $this->entityManager->getRepository(Tickets::class)
                    ->createQueryBuilder('t')
                    ->orderBy('t.price', 'ASC')
                    ->setMaxResults(3)
                    ->getQuery()
                    ->getResult();

                $response = [
                    'type' => 'suggestions',
                    'content' => 'Voici nos meilleures suggestions :',
                    'data' => [
                        'hotels' => array_map([$this, 'formatHotelData'], $hotels),
                        'tickets' => array_map([$this, 'formatTicketData'], $tickets)
                    ]
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'type' => 'text',
                'content' => 'Une erreur est survenue lors du traitement de votre demande.'
            ];
        }

        return $response;
    }
}