<?php

namespace App\Controller;

use App\Entity\Tickets;
use App\Form\TicketsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Promotion;
use App\Entity\User;
use App\Entity\Agency;

#[Route('/offer/ticket')]
class OfferTicketController extends AbstractController
{
    private string $baseImageUrl = 'http://localhost:8000/img/offers/';

    // Déterminer le rôle de l'utilisateur
    private function getUserRole(?User $user): string
    {
        if (!$user) {
            return 'client'; // Utilisateur non connecté = client
        }
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return 'admin';
        }
        if (in_array('ROLE_CLIENT', $roles, true)) {
            return 'client';
        }
        return 'agent';
    }

    // Lister les offres (pour tous les rôles)
    #[Route('/', name: 'app_offer_ticket_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        // Gestion des filtres pour tous les rôles
        $search = $request->query->get('search', '');
        $ticketClasses = $request->query->all()['ticketClasses'] ?? [];
        $ticketTypes = $request->query->all()['ticketTypes'] ?? [];
        $agenciesFilter = $request->query->all()['agencies'] ?? [];

        // Récupérer toutes les agences pour le menu déroulant
        $allAgencies = $entityManager->getRepository(Agency::class)->findAll();

        // Récupérer les offres
        if ($role === 'agent') {
            if (!$user) {
                throw $this->createAccessDeniedException('Utilisateur non connecté.');
            }
            $agencies = $user->getAgencies();
            $tickets = $entityManager->getRepository(Tickets::class)->searchAndFilter(
                $search, 
                $ticketClasses,
                $ticketTypes,
                $agencies,
                $agenciesFilter
            );
        } else {
            // Admin et client voient toutes les offres
            $tickets = $entityManager->getRepository(Tickets::class)->searchAndFilter(
                $search, 
                $ticketClasses,
                $ticketTypes,
                null,
                $agenciesFilter
            );
        }

        // Si c'est une requête AJAX, rendre le partiel
        if ($request->isXmlHttpRequest()) {
            return $this->render("offer_ticket/{$role}/_tickets_list.html.twig", [
                'tickets' => $tickets,
            ]);
        }

        // Rendre la liste selon le rôle
        return $this->render("offer_ticket/{$role}/index.html.twig", [
            'tickets' => $tickets,
            'search' => $search,
            'selected_ticket_classes' => $ticketClasses,
            'selected_ticket_types' => $ticketTypes,
            'role' => $role,
            'all_agencies' => $allAgencies,
            'selected_agencies' => $agenciesFilter,
        ]);
    }

    #[Route('/{action}/{id?}', name: 'app_offer_ticket_action', methods: ['GET', 'POST'])]
    public function action(string $action, ?int $id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $ticket = $id ? $em->getRepository(Tickets::class)->find($id) : new Tickets();
        if (!$ticket && $action !== 'new') {
            throw $this->createNotFoundException('Ticket non trouvé');
        }

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer cette action.');
        }

        // Récupérer les agences de l'utilisateur
        $agencies = $user->getAgencies();
        if ($agencies->isEmpty() && $action !== 'show') {
            throw $this->createAccessDeniedException('Vous devez être associé à une agence pour créer des tickets.');
        }

        // Pour un nouveau ticket, associer la première agence de l'utilisateur
        if ($action === 'new') {
            $ticket->setAgencyId($agencies->first()->getId());
        }

        switch ($action) {
            case 'show':
                return $this->render("offer_ticket/agent/show.html.twig", [
                    'ticket' => $ticket,
                ]);

            case 'new':
            case 'edit':
                $form = $this->createForm(TicketsType::class, $ticket);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    // Gestion de l'image de la compagnie aérienne
                    $imageAirlineFile = $form->get('imageAirline')->getData();
                    if ($imageAirlineFile) {
                        $originalFilename = pathinfo($imageAirlineFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageAirlineFile->guessExtension();

                        try {
                            $imageAirlineFile->move(
                                $this->getParameter('kernel.project_dir') . '/public/Uploads/airlines',
                                $newFilename
                            );
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors du téléchargement de l\'image de la compagnie aérienne.');
                            return $this->redirectToRoute('app_offer_ticket_index');
                        }

                        $ticket->setImageAirline($newFilename);
                    } else if ($action === 'new') {
                        // Si c'est une création et qu'aucune image n'est fournie, utiliser une image par défaut
                        $ticket->setImageAirline('default_airline.png');
                    }

                    // Gestion de l'image de la ville
                    $cityImageFile = $form->get('cityImage')->getData();
                    if ($cityImageFile) {
                        $originalFilename = pathinfo($cityImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$cityImageFile->guessExtension();

                        try {
                            $cityImageFile->move(
                                $this->getParameter('kernel.project_dir') . '/public/Uploads/cities',
                                $newFilename
                            );
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors du téléchargement de l\'image de la ville.');
                            return $this->redirectToRoute('app_offer_ticket_index');
                        }

                        $ticket->setCityImage($newFilename);
                    }

                    $em->persist($ticket);
                    $em->flush();

                    $this->addFlash('success', 'Ticket ' . ($action === 'new' ? 'créé' : 'modifié') . ' avec succès !');
                    return $this->redirectToRoute('app_offer_ticket_index');
                }

                return $this->render("offer_ticket/agent/edit.html.twig", [
                    'ticket' => $ticket,
                    'form' => $form->createView(),
                ]);

            case 'delete':
                if ($this->isCsrfTokenValid('delete'.$ticket->getIdTicket(), $request->request->get('_token'))) {
                    $em->remove($ticket);
                    $em->flush();
                    
                    $this->addFlash('success', 'Ticket supprimé avec succès !');
                }
                return $this->redirectToRoute('app_offer_ticket_index');

            default:
                throw $this->createNotFoundException('Action non reconnue');
        }
    }
} 