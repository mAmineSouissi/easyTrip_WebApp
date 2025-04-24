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

    // Vérifier et mettre à jour les promotions expirées
    private function updateExpiredPromotions(EntityManagerInterface $em): void
    {
        $today = new \DateTime();
        $offers = $em->getRepository(OfferTravel::class)->findBy(['promotion' => null], [], null, 0);

        foreach ($offers as $offer) {
            $promotion = $offer->getPromotion();
            if ($promotion && $promotion->getValidUntil() < $today) {
                // Calculer le prix initial à partir du prix réduit
                $currentPrice = $offer->getPrice();
                $discountPercentage = $promotion->getDiscountPercentage();
                $originalPrice = $currentPrice / (1 - $discountPercentage / 100);

                $offer->setPromotion(null);
                $offer->setPrice(round($originalPrice, 2)); // Arrondir à 2 décimales
                $em->persist($offer);
            }
        }
        $em->flush();
    }

    #[Route('/', name: 'app_offer_travel_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Mettre à jour les promotions expirées
        $this->updateExpiredPromotions($entityManager);

        $offers = $entityManager
            ->getRepository(OfferTravel::class)
            ->findAll();

        return $this->render('offer_travel/index.html.twig', [
            'offer_travels' => $offers,
        ]);
    }

    #[Route('/new', name: 'app_offer_travel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $offerTravel = new OfferTravel();
        $form = $this->createForm(OfferTravelType::class, $offerTravel, [
            'promotions' => $em->getRepository(Promotion::class)->findAll(),
        ]);
        
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $price = $form->get('price')->getData();
            $promotion = $offerTravel->getPromotion();

            // Appliquer la promotion si elle existe
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

        return $this->render('offer_travel/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offer_travel_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, OfferTravel $offerTravel, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    // Calculer le prix initial (avant promotion, si applicable)
    $originalPrice = $offerTravel->getPromotion() 
        ? $offerTravel->getPrice() / (1 - $offerTravel->getPromotion()->getDiscountPercentage() / 100)
        : $offerTravel->getPrice();

    $form = $this->createForm(OfferTravelType::class, $offerTravel, [
        'original_price' => round($originalPrice, 2),
        'promotions' => $em->getRepository(Promotion::class)->findAll(),
        'required_image' => false, // Ajoutez cette ligne
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('imageFile')->getData();
        $newPromotion = $offerTravel->getPromotion();

        // Recalculer le prix si la promotion a changé
        if ($newPromotion) {
            $newPrice = $originalPrice * (1 - $newPromotion->getDiscountPercentage() / 100);
            $offerTravel->setPrice(round($newPrice, 2));
        } else {
            $offerTravel->setPrice(round($originalPrice, 2));
        }
        
        // Traitement de l'image seulement si un nouveau fichier est fourni
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

    return $this->render('offer_travel/edit.html.twig', [
        'form' => $form->createView(),
        'offer_travel' => $offerTravel,
    ]);
}

    #[Route('/{id}', name: 'app_offer_travel_show', methods: ['GET'])]
    public function show(OfferTravel $offerTravel): Response
    {
        return $this->render('offer_travel/show.html.twig', [
            'offer_travel' => $offerTravel,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_offer_travel_delete', methods: ['POST'])]
    public function delete(Request $request, OfferTravel $offerTravel, EntityManagerInterface $em): Response
    {
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
    }

    // OfferTravelController.php
#[Route('/client/listoffers', name: 'app_offer_travel_public_list', methods: ['GET'])]
public function publicList(EntityManagerInterface $em, Request $request): Response
{
    $this->updateExpiredPromotions($em);
    
    $search = $request->query->get('search', '');
    $categories = $request->query->all()['categories'] ?? [];
    $hasPromotion = $request->query->get('hasPromotion');

    // Modification ici - ne filtre que si hasPromotion est true
    $filterPromotion = ($hasPromotion === 'true');

    $offers = $em->getRepository(OfferTravel::class)->searchAndFilter(
        $search, 
        $categories, 
        $filterPromotion ? true : null // Envoie null si false pour désactiver le filtre
    );

    if ($request->isXmlHttpRequest()) {
        return $this->render('offer_travel/_offers_list.html.twig', [
            'offer_travels' => $offers
        ]);
    }

    return $this->render('offer_travel/public_list.html.twig', [
        'offer_travels' => $offers,
        'search' => $search,
        'selected_categories' => $categories,
        'has_promotion' => $hasPromotion === 'true',
    ]);
}

    #[Route('/client/offer/{id}', name: 'app_offer_travel_public_show', methods: ['GET'], requirements: ['id' => '\d+'])]    
    public function publicShow(int $id, EntityManagerInterface $em): Response
    {
        $this->updateExpiredPromotions($em);
        $offer = $em->getRepository(OfferTravel::class)->find($id);
        
        if (!$offer) {
            throw $this->createNotFoundException('Offre non trouvée');
        }

        return $this->render('offer_travel/public_show.html.twig', [
            'offer_travel' => $offer,
        ]);
    }
}