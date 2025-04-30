<?php

namespace App\Controller;

use App\Entity\Tickets;
use App\Form\TicketSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;

#[Route('/voyages')]
class ClientTicketsController extends AbstractController
{
    #[Route('/', name: 'app_client_tickets_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketSearchType::class);
        $form->handleRequest($request);

        $queryBuilder = $entityManager
            ->getRepository(Tickets::class)
            ->createQueryBuilder('t');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!empty($data['departureCity'])) {
                $queryBuilder->andWhere('t.departureCity LIKE :departureCity')
                    ->setParameter('departureCity', '%'.$data['departureCity'].'%');
            }

            if (!empty($data['arrivalCity'])) {
                $queryBuilder->andWhere('t.arrivalCity LIKE :arrivalCity')
                    ->setParameter('arrivalCity', '%'.$data['arrivalCity'].'%');
            }

            if (!empty($data['departureDate'])) {
                $queryBuilder->andWhere('t.departureDate = :departureDate')
                    ->setParameter('departureDate', $data['departureDate']);
            }

            if (!empty($data['ticketClass'])) {
                $queryBuilder->andWhere('t.ticketClass = :ticketClass')
                    ->setParameter('ticketClass', $data['ticketClass']);
            }

            if (!empty($data['maxPrice'])) {
                $queryBuilder->andWhere('t.price <= :maxPrice')
                    ->setParameter('maxPrice', $data['maxPrice']);
            }
        }

        $tickets = $queryBuilder->getQuery()->getResult();

        return $this->render('client_tickets/index.html.twig', [
            'tickets' => $tickets,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idTicket}', name: 'app_client_tickets_show', methods: ['GET'])]
    public function show(Tickets $ticket): Response
    {
        // Fetch weather data for the arrival city
        $weatherData = null;
        $apiKey = $this->getParameter('openweathermap_api_key');
        $client = HttpClient::create();

        try {
            $response = $client->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q' => $ticket->getArrivalCity(),
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $weatherData = $response->toArray();
            }
        } catch (\Exception $e) {
            $weatherData = null;
        }

        return $this->render('client_tickets/show.html.twig', [
            'ticket' => $ticket,
            'weather' => $weatherData,
        ]);
    }

    #[Route('/statistiques', name: 'app_client_tickets_statistics', methods: ['GET'])]
    public function statistics(EntityManagerInterface $entityManager): Response
    {
        // Statistiques par classe de ticket
        $classStats = $entityManager->createQuery(
            "SELECT t.ticketClass as class, COUNT(t.idTicket) as count, AVG(t.price) as avgPrice 
             FROM App\Entity\Tickets t 
             GROUP BY t.ticketClass"
        )->getResult();

        // Statistiques par ville de destination
        $cityStats = $entityManager->createQuery(
            "SELECT t.arrivalCity as city, COUNT(t.idTicket) as count 
             FROM App\Entity\Tickets t 
             GROUP BY t.arrivalCity 
             ORDER BY count DESC"
        )->setMaxResults(5)
         ->getResult();

        // Prix moyen par mois (version corrigée avec SUBSTRING)
        $monthlyStats = $entityManager->createQuery(
            "SELECT SUBSTRING(t.departureDate, 6, 2) as month, AVG(t.price) as avgPrice 
             FROM App\Entity\Tickets t 
             WHERE t.departureDate IS NOT NULL 
             GROUP BY month 
             ORDER BY month"
        )->getResult();

        // Conversion des mois en nombres pour le traitement
        $monthlyStats = array_map(function($item) {
            return [
                'month' => (int)$item['month'],
                'avgPrice' => $item['avgPrice']
            ];
        }, $monthlyStats);

        return $this->render('client_tickets/statistics.html.twig', [
            'classStats' => $classStats,
            'cityStats' => $cityStats,
            'monthlyStats' => $monthlyStats,
        ]);
    }
}