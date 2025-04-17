<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/promotion')]
final class PromotionController extends AbstractController
{
    #[Route(name: 'app_promotion_index', methods: ['GET'])]
    public function index(PromotionRepository $promotionRepository): Response
    {
        $promotions = $promotionRepository->findActivePromotions();
            
        return $this->render('promotion/index.html.twig', [
            'promotions' => $promotions
        ]);
    }

    #[Route('/new', name: 'app_promotion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($promotion);
            $entityManager->flush();

            return $this->redirectToRoute('app_promotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('promotion/new.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_promotion_show', methods: ['GET'])]
    public function show(Promotion $promotion): Response
    {
        return $this->render('promotion/show.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_promotion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Promotion $promotion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_promotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('promotion/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_promotion_delete', methods: ['POST'])]
    public function delete(Request $request, Promotion $promotion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promotion->getId(), $request->getPayload()->getString('_token'))) {
            // Mettre à jour les offres associées
            $offers = $entityManager->getRepository(\App\Entity\OfferTravel::class)->findBy(['promotion' => $promotion]);
            foreach ($offers as $offer) {
                $currentPrice = $offer->getPrice();
                $discountPercentage = $promotion->getDiscountPercentage();
                // Calculer le prix initial : prix actuel / (1 - pourcentage de réduction)
                $originalPrice = $currentPrice / (1 - $discountPercentage / 100);
                $offer->setPrice(round($originalPrice, 2)); // Arrondir à 2 décimales
                $offer->setPromotion(null); // Dissocier la promotion
                $entityManager->persist($offer);
            }
            // Appliquer les changements avant de supprimer la promotion
            $entityManager->flush();

            // Supprimer la promotion
            $entityManager->remove($promotion);
            $entityManager->flush();

            $this->addFlash('success', 'Promotion supprimée et prix des offres restaurés avec succès !');
        }

        return $this->redirectToRoute('app_promotion_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/agent/list', name: 'app_promotion_public', methods: ['GET'])]
    public function index_list(PromotionRepository $promotionRepository): Response
    {
        $promotions = $promotionRepository->findActivePromotions();
    
        return $this->render('promotion/public_list.html.twig', [
            'promotions' => $promotions,
        ]);
    }
}