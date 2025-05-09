<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\User;
use App\Entity\Agency;

#[Route('/hotel')]
class HotelController extends AbstractController
{
    private string $baseImageUrl = 'http://localhost:8000/img/hotels/';

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
    #[Route('/', name: 'app_hotel_index', methods: ['GET'])]
    public function index(HotelRepository $hotelRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        // Gestion des filtres pour tous les rôles
        $search = $request->query->get('search', '');
        $stars = $request->query->all()['stars'] ?? [];

        // Récupérer les offres selon le rôle
        if ($role === 'agent') {
            if (!$user) {
                throw $this->createAccessDeniedException('Utilisateur non connecté.');
            }
            $agencies = $user->getAgencies();
            if ($agencies->isEmpty()) {
                throw $this->createAccessDeniedException('Vous devez être associé à une agence pour voir les hôtels.');
            }
            // Récupérer uniquement les hôtels des agences de l'utilisateur
            $hotels = $hotelRepository->findByAgencies($agencies->toArray());
        } else {
            // Admin et client voient toutes les offres
            $hotels = $hotelRepository->findAll();
        }

        // Si c'est une requête AJAX, rendre le partiel
        if ($request->isXmlHttpRequest()) {
            return $this->render("hotels/{$role}/_hotels_list.html.twig", [
                'hotels' => $hotels,
            ]);
        }

        // Rendre la liste selon le rôle
        return $this->render("hotels/{$role}/index.html.twig", [
            'hotels' => $hotels,
            'search' => $search,
            'selected_stars' => $stars,
            'role' => $role,
        ]);
    }

    #[Route('/{action}/{id?}', name: 'app_hotel_action', methods: ['GET', 'POST'])]
    public function action(string $action, ?int $id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $hotel = $id ? $em->getRepository(Hotel::class)->find($id) : new Hotel();
        if (!$hotel && $action !== 'new') {
            throw $this->createNotFoundException('Hôtel non trouvé');
        }

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer cette action.');
        }

        // Récupérer les agences de l'utilisateur
        $agencies = $user->getAgencies();
        if ($agencies->isEmpty() && $action !== 'show') {
            throw $this->createAccessDeniedException('Vous devez être associé à une agence pour gérer les hôtels.');
        }

        // Vérifier si l'utilisateur a le droit d'accéder à cet hôtel
        if ($action !== 'new' && $hotel->getAgency() && !$agencies->contains($hotel->getAgency())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet hôtel.');
        }

        // Pour un nouvel hôtel, associer la première agence de l'utilisateur
        if ($action === 'new') {
            $firstAgency = $agencies->first();
            if (!$firstAgency) {
                throw $this->createAccessDeniedException('Aucune agence trouvée pour cet utilisateur.');
            }
            $hotel->setAgency($firstAgency);
        }

        switch ($action) {
            case 'show':
                return $this->render("hotels/agent/show.html.twig", [
                    'hotel' => $hotel,
                ]);

            case 'new':
            case 'edit':
                $form = $this->createForm(HotelType::class, $hotel);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    // Gestion de l'image de l'hôtel
                    $imageFile = $form->get('image')->getData();
                    if ($imageFile) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                        try {
                            $imageFile->move(
                                $this->getParameter('kernel.project_dir') . '/public/Uploads/hotels',
                                $newFilename
                            );
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors du téléchargement de l\'image de l\'hôtel.');
                            return $this->redirectToRoute('app_hotel_index');
                        }

                        $hotel->setImage($newFilename);
                    }

                    $em->persist($hotel);
                    $em->flush();

                    $this->addFlash('success', 'Hôtel ' . ($action === 'new' ? 'créé' : 'modifié') . ' avec succès !');
                    return $this->redirectToRoute('app_hotel_index');
                }

                return $this->render("hotels/agent/edit.html.twig", [
                    'hotel' => $hotel,
                    'form' => $form->createView(),
                ]);

            case 'delete':
                if ($this->isCsrfTokenValid('delete'.$hotel->getId(), $request->request->get('_token'))) {
                    $em->remove($hotel);
                    $em->flush();
                    
                    $this->addFlash('success', 'Hôtel supprimé avec succès !');
                }
                return $this->redirectToRoute('app_hotel_index');

            default:
                throw $this->createNotFoundException('Action non reconnue');
        }
    }
} 