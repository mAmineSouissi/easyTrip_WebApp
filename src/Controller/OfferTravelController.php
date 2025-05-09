<?php

namespace App\Controller;

use App\Entity\OfferTravel;
use App\Form\OfferTravelType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Promotion;
use App\Entity\User;
use App\Entity\Agency; // Ajout de l'import pour Agency

#[Route('/offer/travel')]
class OfferTravelController extends AbstractController
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

    // Mettre à jour les promotions expirées
    private function updateExpiredPromotions(EntityManagerInterface $em): void
    {
        $today = new \DateTime();
        $offers = $em->getRepository(OfferTravel::class)->findBy(['promotion' => null], [], null, 0);

        foreach ($offers as $offer) {
            $promotion = $offer->getPromotion();
            if ($promotion && $promotion->getValidUntil() < $today) {
                $currentPrice = $offer->getPrice();
                $discountPercentage = $promotion->getDiscountPercentage();
                $originalPrice = $currentPrice / (1 - $discountPercentage / 100);

                $offer->setPromotion(null);
                $offer->setPrice(round($originalPrice, 2));
                $em->persist($offer);
            }
        }
        $em->flush();
    }

    // Lister les offres (pour tous les rôles)
    #[Route('/', name: 'app_offer_travel_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        $this->updateExpiredPromotions($entityManager);

        // Gestion des filtres pour tous les rôles
        $search = $request->query->get('search', '');
        $categories = $request->query->all()['categories'] ?? [];
        $hasPromotion = $request->query->get('hasPromotion');
        $filterPromotion = ($hasPromotion === 'true');
        $agenciesFilter = $request->query->all()['agencies'] ?? []; // Nouveau paramètre pour filtrer par agences

        // Récupérer toutes les agences pour le menu déroulant
        $allAgencies = $entityManager->getRepository(Agency::class)->findAll();

        // Récupérer les offres
        if ($role === 'agent') {
            if (!$user) {
                throw $this->createAccessDeniedException('Utilisateur non connecté.');
            }
            $agencies = $user->getAgencies();
            $offers = $entityManager->getRepository(OfferTravel::class)->searchAndFilter(
                $search, 
                $categories, 
                $filterPromotion ? true : null,
                $agencies,
                $agenciesFilter // Passer le filtre des agences
            );
        } else {
            // Admin et client voient toutes les offres
            $offers = $entityManager->getRepository(OfferTravel::class)->searchAndFilter(
                $search, 
                $categories, 
                $filterPromotion ? true : null,
                null,
                $agenciesFilter // Passer le filtre des agences
            );
        }

        // Si c'est une requête AJAX, rendre le partiel
        if ($request->isXmlHttpRequest()) {
            return $this->render("offer_travel/{$role}/_offers_list.html.twig", [
                'offer_travels' => $offers,
            ]);
        }

        // Rendre la liste selon le rôle
        return $this->render("offer_travel/{$role}/index.html.twig", [
            'offer_travels' => $offers,
            'search' => $search,
            'selected_categories' => $categories,
            'has_promotion' => $hasPromotion === 'true',
            'role' => $role,
            'all_agencies' => $allAgencies, // Passer les agences au template
            'selected_agencies' => $agenciesFilter, // Passer les agences sélectionnées
        ]);
    }

    // Gérer les actions (new, edit, offer, delete)
    #[Route('/{action}/{id?}', name: 'app_offer_travel_action', methods: ['GET', 'POST'], requirements: ['action' => 'new|edit|offer|delete', 'id' => '\d+'])]
    public function handleAction(string $action, ?int $id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        // Seuls les agents peuvent faire new, edit, delete
        if (in_array($action, ['new', 'edit', 'delete']) && $role !== 'agent') {
            throw $this->createAccessDeniedException('Seuls les agents peuvent effectuer cette action.');
        }

        // Charger l'offre si nécessaire (pour edit, offer, delete)
        $offerTravel = null;
        if ($action !== 'new') {
            $offerTravel = $em->getRepository(OfferTravel::class)->find($id);
            if (!$offerTravel) {
                throw $this->createNotFoundException('Offre non trouvée');
            }

            // Vérifier que l'agent agit sur sa propre offre (pour toutes les actions)
            if ($role === 'agent' && $offerTravel->getAgency()->getUser() !== $user) {
                $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette offre.');
                return $this->redirectToRoute('app_offer_travel_index');
            }
        }

        // Gérer les différentes actions
        switch ($action) {
            case 'new':
                $offerTravel = new OfferTravel();
                $form = $this->createForm(OfferTravelType::class, $offerTravel, [
                    'promotions' => $em->getRepository(Promotion::class)->findAll(),
                ]);
                
                $form->handleRequest($request);
            
                if ($form->isSubmitted() && $form->isValid()) {
                    $imageFile = $form->get('imageFile')->getData();
                    $price = $form->get('price')->getData();
                    $promotion = $offerTravel->getPromotion();

                    if ($promotion) {
                        $discountedPrice = $price * (1 - $promotion->getDiscountPercentage() / 100);
                        $offerTravel->setPrice(round($discountedPrice, 2));
                    } else {
                        $offerTravel->setPrice($price);
                    }
                    
                    if ($imageFile) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                        try {
                            $imageFile->move(
                                $this->getParameter('offers_images_directory'),
                                $newFilename
                            );
                            $offerTravel->setImage($this->baseImageUrl . $newFilename);
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                        }
                    }

                    $em->persist($offerTravel);
                    $em->flush();

                    $this->addFlash('success', 'Offre créée avec succès !');
                    return $this->redirectToRoute('app_offer_travel_index');
                }

                return $this->render("offer_travel/{$role}/new.html.twig", [
                    'form' => $form->createView(),
                ]);

            case 'edit':
                $originalPrice = $offerTravel->getPromotion() 
                    ? $offerTravel->getPrice() / (1 - $offerTravel->getPromotion()->getDiscountPercentage() / 100)
                    : $offerTravel->getPrice();

                $form = $this->createForm(OfferTravelType::class, $offerTravel, [
                    'original_price' => round($originalPrice, 2),
                    'promotions' => $em->getRepository(Promotion::class)->findAll(),
                    'required_image' => false,
                ]);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $imageFile = $form->get('imageFile')->getData();
                    $newPromotion = $offerTravel->getPromotion();

                    if ($newPromotion) {
                        $newPrice = $originalPrice * (1 - $newPromotion->getDiscountPercentage() / 100);
                        $offerTravel->setPrice(round($newPrice, 2));
                    } else {
                        $offerTravel->setPrice(round($originalPrice, 2));
                    }
                    
                    if ($imageFile) {
                        if ($offerTravel->getImage()) {
                            $oldFilename = basename($offerTravel->getImage());
                            $oldPath = $this->getParameter('offers_images_directory').'/'.$oldFilename;
                            if (file_exists($oldPath)) {
                                unlink($oldPath);
                            }
                        }

                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                        try {
                            $imageFile->move(
                                $this->getParameter('offers_images_directory'),
                                $newFilename
                            );
                            $offerTravel->setImage($this->baseImageUrl . $newFilename);
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                        }
                    }

                    $em->flush();

                    $this->addFlash('success', 'Offre modifiée avec succès !');
                    return $this->redirectToRoute('app_offer_travel_index');
                }

                return $this->render("offer_travel/{$role}/edit.html.twig", [
                    'form' => $form->createView(),
                    'offer_travel' => $offerTravel,
                ]);

            case 'offer':
                $this->updateExpiredPromotions($em);
                return $this->render("offer_travel/{$role}/show.html.twig", [
                    'offer_travel' => $offerTravel,
                    'role' => $role,
                ]);

            case 'delete':
                if ($this->isCsrfTokenValid('delete'.$offerTravel->getId(), $request->request->get('_token'))) {
                    if ($offerTravel->getImage()) {
                        $filename = basename($offerTravel->getImage());
                        $imagePath = $this->getParameter('offers_images_directory').'/'.$filename;
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                    
                    $em->remove($offerTravel);
                    $em->flush();
                    
                    $this->addFlash('success', 'Offre supprimée avec succès !');
                }
                return $this->redirectToRoute('app_offer_travel_index');

            default:
                throw $this->createNotFoundException('Action non reconnue');
        }
    }

    // Statistiques (admin seulement)
    #[Route('/admin/statistics', name: 'app_offer_travel_statistics', methods: ['GET'])]
    public function statistics(EntityManagerInterface $em): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        if ($role !== 'admin') {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs.');
        }

        $offersByAgency = $em->createQuery(
            "SELECT a.name as agencyName, COUNT(o.id) as offersCount
             FROM App\Entity\OfferTravel o
             JOIN o.agency a
             GROUP BY a.id
             ORDER BY offersCount DESC"
        )->getResult();

        $revenueByAgency = $em->createQuery(
            "SELECT a.name as agencyName, SUM(o.price) as totalRevenue
             FROM App\Entity\OfferTravel o
             JOIN o.agency a
             GROUP BY a.id
             ORDER BY totalRevenue DESC"
        )->getResult();

        $offersByCategory = $em->createQuery(
            "SELECT o.category as categoryName, COUNT(o.id) as offersCount
             FROM App\Entity\OfferTravel o
             GROUP BY o.category
             ORDER BY offersCount DESC"
        )->getResult();

        return $this->render('offer_travel/admin/statistics.html.twig', [
            'offersByAgency' => [
                'labels' => array_column($offersByAgency, 'agencyName'),
                'data' => array_column($offersByAgency, 'offersCount')
            ],
            'revenueByAgency' => [
                'labels' => array_column($revenueByAgency, 'agencyName'),
                'data' => array_column($revenueByAgency, 'totalRevenue')
            ],
            'offersByCategory' => [
                'labels' => array_column($offersByCategory, 'categoryName'),
                'data' => array_column($offersByCategory, 'offersCount')
            ]
        ]);
    }
}