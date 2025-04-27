<?php

namespace App\Controller;

use App\Entity\Tickets;
use App\Form\TicketsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/tickets')]
class TicketsController extends AbstractController
{
    #[Route('/', name: 'app_tickets_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tickets = $entityManager
            ->getRepository(Tickets::class)
            ->findAll();

        return $this->render('tickets/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/new', name: 'app_tickets_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticket = new Tickets();
        $form = $this->createForm(TicketsType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle imageAirline upload
            $imageAirlineFile = $form->get('imageAirline')->getData();
            if ($imageAirlineFile) {
                $newFilename = uniqid().'.'.$imageAirlineFile->guessExtension();
                try {
                    $imageAirlineFile->move(
                        $this->getParameter('airlines_directory'),
                        $newFilename
                    );
                    $ticket->setImageAirline($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du logo de la compagnie.');
                }
            } else {
                // Set default or null if no file is uploaded
                $ticket->setImageAirline(null);
            }

            // Handle cityImage upload
            $cityImageFile = $form->get('cityImage')->getData();
            if ($cityImageFile) {
                $newFilename = uniqid().'.'.$cityImageFile->guessExtension();
                try {
                    $cityImageFile->move(
                        $this->getParameter('cities_directory'),
                        $newFilename
                    );
                    $ticket->setCityImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image de la ville.');
                }
            } else {
                // Set default or null if no file is uploaded
                $ticket->setCityImage(null);
            }

            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('app_tickets_index');
        }

        return $this->render('tickets/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idTicket}', name: 'app_tickets_show', methods: ['GET'])]
    public function show(Tickets $ticket): Response
    {
        return $this->render('tickets/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{idTicket}/edit', name: 'app_tickets_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tickets $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketsType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle imageAirline upload
            $imageAirlineFile = $form->get('imageAirline')->getData();
            if ($imageAirlineFile) {
                $newFilename = uniqid().'.'.$imageAirlineFile->guessExtension();
                try {
                    $imageAirlineFile->move(
                        $this->getParameter('airlines_directory'),
                        $newFilename
                    );
                    $ticket->setImageAirline($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du logo de la compagnie.');
                }
            }

            // Handle cityImage upload
            $cityImageFile = $form->get('cityImage')->getData();
            if ($cityImageFile) {
                $newFilename = uniqid().'.'.$cityImageFile->guessExtension();
                try {
                    $cityImageFile->move(
                        $this->getParameter('cities_directory'),
                        $newFilename
                    );
                    $ticket->setCityImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image de la ville.');
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_tickets_index');
        }

        return $this->render('tickets/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idTicket}', name: 'app_tickets_delete', methods: ['POST'])]
    public function delete(Request $request, Tickets $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getIdTicket(), $request->request->get('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tickets_index');
    }
}