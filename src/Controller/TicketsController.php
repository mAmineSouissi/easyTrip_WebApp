<?php

namespace App\Controller;

use App\Entity\Tickets;
use App\Entity\Agency;
use App\Form\TicketsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/tickets')]
class TicketsController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private ParameterBagInterface $params
    ) {}

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
            $this->handleFileUploads($form, $ticket);
            
            $entityManager->persist($ticket);
            $entityManager->flush();

            $emailResults = $this->notifyAgencies($ticket, $entityManager);

            if ($emailResults['total'] > 0) {
                $this->addFlash(
                    'success',
                    sprintf('Ticket créé et %d/%d emails envoyés aux agences avec succès !', 
                        $emailResults['success'], $emailResults['total'])
                );
                
                if ($emailResults['failed'] > 0) {
                    $this->addFlash(
                        'warning',
                        sprintf('%d emails n\'ont pas pu être envoyés', $emailResults['failed'])
                    );
                }
            } else {
                $this->addFlash('warning', 'Aucun email n\'a été envoyé car aucune agence n\'est enregistrée.');
            }

            return $this->redirectToRoute('app_tickets_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tickets/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function handleFileUploads($form, Tickets $ticket): void
    {
        $uploadDir = $this->params->get('kernel.project_dir').'/public/uploads/';
        
        // Logo compagnie aérienne
        $airlineImage = $form->get('imageAirline')->getData();
        if ($airlineImage) {
            $newFilename = uniqid().'.'.$airlineImage->guessExtension();
            try {
                $airlineImage->move($uploadDir.'airlines/', $newFilename);
                $ticket->setImageAirline($newFilename);
            } catch (FileException $e) {
                $this->logger->error('Erreur upload logo: '.$e->getMessage());
                $this->addFlash('error', 'Erreur lors du téléchargement du logo');
            }
        }

        // Image de la ville
        $cityImage = $form->get('cityImage')->getData();
        if ($cityImage) {
            $newFilename = uniqid().'.'.$cityImage->guessExtension();
            try {
                $cityImage->move($uploadDir.'cities/', $newFilename);
                $ticket->setCityImage($newFilename);
            } catch (FileException $e) {
                $this->logger->error('Erreur upload ville: '.$e->getMessage());
                $this->addFlash('error', 'Erreur lors du téléchargement de l\'image de ville');
            }
        }
    }

    private function notifyAgencies(Tickets $ticket, EntityManagerInterface $em): array
    {
        $agencies = $em->getRepository(Agency::class)->findAll();
        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => count($agencies)
        ];

        foreach ($agencies as $agency) {
            try {
                $email = (new Email())
                    ->from(new Address(
                        $this->params->get('mailer_from', 'youssefcarma@gmail.com'),
                        $this->params->get('mailer_name', 'EasyTrip')
                    ))
                    ->to($agency->getEmail())
                    ->subject('Nouveau ticket disponible !')
                    ->html($this->renderView('emails/new_ticket.html.twig', [
                        'ticket' => $ticket,
                        'agency' => $agency
                    ]));

                $this->mailer->send($email);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $this->logger->error('Échec envoi email à '.$agency->getEmail().': '.$e->getMessage());
            }
        }

        return $results;
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
            $this->handleFileUploads($form, $ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Ticket mis à jour avec succès !');
            return $this->redirectToRoute('app_tickets_index', [], Response::HTTP_SEE_OTHER);
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
            $this->addFlash('success', 'Ticket supprimé avec succès !');
        }

        return $this->redirectToRoute('app_tickets_index', [], Response::HTTP_SEE_OTHER);
    }
}