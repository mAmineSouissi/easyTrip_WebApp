<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/promotion')]
final class PromotionController extends AbstractController
{
    #[Route(name: 'app_promotion_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $promotions = $entityManager
            ->getRepository(Promotion::class)
            ->findAll();

        return $this->render('promotion/index.html.twig', [
            'promotions' => $promotions,
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

    #[Route('/filter', name: 'app_promotion_filter', methods: ['POST'])]
    public function filter(Request $request, PromotionRepository $promotionRepository, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $searchTerm = $data['search'] ?? '';
        $discountRange = $data['discountRange'] ?? 'all';
        $selectedDate = $data['date'] ?? null;

        $qb = $promotionRepository->createQueryBuilder('p');

        // Filtre par nom
        if ($searchTerm) {
            $qb->andWhere('LOWER(p.title) LIKE :search')
               ->setParameter('search', '%' . strtolower($searchTerm) . '%');
        }

        // Filtre par pourcentage de réduction
        if ($discountRange !== 'all') {
            $range = explode('-', $discountRange);
            $min = (float)$range[0];
            $max = (float)$range[1];
            $qb->andWhere('p.discount_percentage >= :min AND p.discount_percentage <= :max')
               ->setParameter('min', $min)
               ->setParameter('max', $max);
        }

        // Filtre par date
        if ($selectedDate) {
            $qb->andWhere('p.valid_until >= :date')
               ->setParameter('date', new \DateTime($selectedDate));
        }

        $promotions = $qb->getQuery()->getResult();

        // Préparer les données pour le JSON
        $promotionData = array_map(function ($promotion) use ($csrfTokenManager) {
            $validUntil = $promotion->getValidUntil();
            return [
                'id' => $promotion->getId(),
                'title' => $promotion->getTitle(),
                'discountPercentage' => $promotion->getDiscountPercentage(),
                'validUntil' => $validUntil->format('Y-m-d'),
                'validUntilFormatted' => $validUntil->format('d/m/Y'),
                'isActive' => $validUntil >= new \DateTime(),
                'csrfToken' => $csrfTokenManager->getToken('delete'.$promotion->getId())->getValue(),
                'showUrl' => $this->generateUrl('app_promotion_show', ['id' => $promotion->getId()]),
                'editUrl' => $this->generateUrl('app_promotion_edit', ['id' => $promotion->getId()]),
                'deleteUrl' => $this->generateUrl('app_promotion_delete', ['id' => $promotion->getId()])
            ];
        }, $promotions);

        return new JsonResponse(['promotions' => $promotionData]);
    }
}