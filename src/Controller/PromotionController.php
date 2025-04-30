<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/promotion')]
final class PromotionController extends AbstractController
{
    #[Route(name: 'app_promotion_index', methods: ['GET'])]
    public function index(
        PromotionRepository $promotionRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $promotionRepository->createQueryBuilder('p')
            ->orderBy('p.valid_until', 'DESC')
            ->getQuery();
            
        $promotions = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('promotion/admin/index.html.twig', [
            'promotions' => $promotions
        ]);
    }

    #[Route('/new', name: 'app_promotion_new', methods: ['GET', 'POST'])]
public function new(
    Request $request, 
    EntityManagerInterface $entityManager,
    \Symfony\Component\Mailer\MailerInterface $mailer
): Response {
    $promotion = new Promotion();
    $form = $this->createForm(PromotionType::class, $promotion);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($promotion);
        $entityManager->flush();

        // Récupérer toutes les agences
        $agencies = $entityManager->getRepository(\App\Entity\Agency::class)->findAll();
        
        // Envoyer un email à chaque agence
        foreach ($agencies as $agency) {
            $email = (new Email())
                ->from('youssefcarma@gmail.com')
                ->to($agency->getEmail())
                ->subject('Nouvelle promotion disponible !')
                ->html($this->renderView(
                    'emails/new_promotion.html.twig',
                    ['promotion' => $promotion, 'agency' => $agency]
                ));

            $mailer->send($email);
        }

        $this->addFlash('success', 'Promotion créée et emails envoyés aux agences avec succès !');
        return $this->redirectToRoute('app_promotion_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('promotion/admin/new.html.twig', [
        'promotion' => $promotion,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_promotion_show', methods: ['GET'])]
    public function show(Promotion $promotion): Response
    {
        return $this->render('promotion/admin/show.html.twig', [
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

        return $this->render('promotion/admin/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_promotion_delete', methods: ['POST'])]
    public function delete(Request $request, Promotion $promotion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promotion->getId(), $request->getPayload()->getString('_token'))) {
            $offers = $entityManager->getRepository(\App\Entity\OfferTravel::class)->findBy(['promotion' => $promotion]);
            foreach ($offers as $offer) {
                $currentPrice = $offer->getPrice();
                $discountPercentage = $promotion->getDiscountPercentage();
                $originalPrice = $currentPrice / (1 - $discountPercentage / 100);
                $offer->setPrice(round($originalPrice, 2));
                $offer->setPromotion(null);
                $entityManager->persist($offer);
            }
            $entityManager->flush();
            $entityManager->remove($promotion);
            $entityManager->flush();

            $this->addFlash('success', 'Promotion supprimée et prix des offres restaurés avec succès !');
        }

        return $this->redirectToRoute('app_promotion_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/agent/list', name: 'app_promotion_public', methods: ['GET'])]
    public function index_list(
        PromotionRepository $promotionRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $promotionRepository->createQueryBuilder('p')
            ->where('p.valid_until >= :currentDate')
            ->setParameter('currentDate', new \DateTime())
            ->orderBy('p.valid_until', 'ASC')
            ->getQuery();
            
        $promotions = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );
    
        return $this->render('promotion/agent/public_list.html.twig', [
            'promotions' => $promotions,
        ]);
    }
}