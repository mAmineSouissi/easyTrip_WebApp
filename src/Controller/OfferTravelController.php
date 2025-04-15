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

#[Route('/offer/travel')]
class OfferTravelController extends AbstractController
{
    private string $baseImageUrl = 'http://localhost:8000/img/offers/';

    // Route pour lister toutes les offres (admin)
    #[Route('/', name: 'app_offer_travel_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $offers = $entityManager
            ->getRepository(OfferTravel::class)
            ->findAll();

        return $this->render('offer_travel/index.html.twig', [
            'offer_travels' => $offers,
        ]);
    }

    // Route pour créer une nouvelle offre (admin)
    #[Route('/new', name: 'app_offer_travel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $offerTravel = new OfferTravel();
        $form = $this->createForm(OfferTravelType::class, $offerTravel, [
            'promotions' => $em->getRepository(Promotion::class)->findAll(),
        ]);
        
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Appliquer la promotion si elle existe
            $promotion = $offerTravel->getPromotion();
            if ($promotion) {
                $originalPrice = $offerTravel->getPrice();
                $discountedPrice = $originalPrice * (1 - $promotion->getDiscountPercentage() / 100);
                $offerTravel->setPrice($discountedPrice);
            }
            $imageFile = $form->get('imageFile')->getData();
            
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

            // Si une promotion est sélectionnée, on stocke le prix déjà calculé (avec la réduction)
            // Le calcul a été fait côté client via JavaScript
            $em->persist($offerTravel);
            $em->flush();

            $this->addFlash('success', 'Offre créée avec succès !');
            return $this->redirectToRoute('app_offer_travel_index');
        }

        return $this->render('offer_travel/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour éditer une offre (admin)
    #[Route('/{id}/edit', name: 'app_offer_travel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OfferTravel $offerTravel, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // Calcul du prix original avant promotion (si une promotion est active)
       // Stocker le prix original avant toute modification
    $originalPrice = $offerTravel->getPromotion() ? 
    $offerTravel->getPrice() / (1 - $offerTravel->getPromotion()->getDiscountPercentage() / 100) : 
    $offerTravel->getPrice();

$form = $this->createForm(OfferTravelType::class, $offerTravel, [
    'original_price' => $originalPrice,
    'promotions' => $em->getRepository(Promotion::class)->findAll(),
]);

$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    // Recalculer le prix si la promotion a changé
    $newPromotion = $offerTravel->getPromotion();
    if ($newPromotion) {
        $newPrice = $originalPrice * (1 - $newPromotion->getDiscountPercentage() / 100);
        $offerTravel->setPrice($newPrice);
    } else {
        // Si pas de promotion, utiliser le prix original
        $offerTravel->setPrice($originalPrice);
    }
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                // Supprimer ancienne image si elle existe
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

            // Le prix est déjà calculé avec la promotion (si applicable) via JavaScript
            $em->flush();

            $this->addFlash('success', 'Offre modifiée avec succès !');
            return $this->redirectToRoute('app_offer_travel_index');
        }

        return $this->render('offer_travel/edit.html.twig', [
            'form' => $form->createView(),
            'offer_travel' => $offerTravel,
        ]);
    }

    // Route pour voir le détail d'une offre (admin)
    #[Route('/{id}', name: 'app_offer_travel_show', methods: ['GET'])]
    public function show(OfferTravel $offerTravel): Response
    {
        return $this->render('offer_travel/show.html.twig', [
            'offer_travel' => $offerTravel,
        ]);
    }

    // Route pour supprimer une offre (admin)
    #[Route('/{id}/delete', name: 'app_offer_travel_delete', methods: ['POST'])]
    public function delete(Request $request, OfferTravel $offerTravel, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offerTravel->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée
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
    }

    // Route pour lister les offres (public)
    #[Route('/client/listoffers', name: 'app_offer_travel_public_list', methods: ['GET'])]
    public function publicList(EntityManagerInterface $em): Response
    {
        $offers = $em->getRepository(OfferTravel::class)->findAll();
        return $this->render('offer_travel/public_list.html.twig', [
            'offer_travels' => $offers,
        ]);
    }

    // Route pour voir le détail d'une offre (public)
    #[Route('/client/offer/{id}', name: 'app_offer_travel_public_show', methods: ['GET'], requirements: ['id' => '\d+'])]    
    public function publicShow(int $id, EntityManagerInterface $em): Response
    {
        $offer = $em->getRepository(OfferTravel::class)->find($id);
        
        if (!$offer) {
            throw $this->createNotFoundException('Offre non trouvée');
        }

        return $this->render('offer_travel/public_show.html.twig', [
            'offer_travel' => $offer,
        ]);
    }
}