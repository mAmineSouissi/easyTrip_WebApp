<?php

namespace App\Controller;

use App\Entity\Tickets;
use App\Form\TicketSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        return $this->render('client_tickets/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }
}